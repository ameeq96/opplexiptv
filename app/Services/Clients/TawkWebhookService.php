<?php

namespace App\Services\Clients;

use App\Models\CustomerActivity;
use App\Models\CustomerConversation;
use App\Models\CustomerMessage;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TawkWebhookService
{
    public function __construct(
        private CustomerActivityService $activities,
        private CustomerIdentityService $identity,
    ) {}

    public function receive(array $data, string $eventId, string $payloadHash): bool
    {
        return DB::transaction(function () use ($data, $eventId, $payloadHash) {
            $receipt = CustomerActivity::query()
                ->where('source', 'tawk_webhook')
                ->where('event_id', $eventId)
                ->first();
            if ($receipt !== null) {
                abort_unless(hash_equals($receipt->payload_hash, $payloadHash), 409, 'Event identity already has different contents.');
                return false;
            }

            $isTranscript = $data['event'] === 'chat:transcript_created';
            $chatId = $isTranscript ? $data['chat']['id'] : $data['chatId'];
            $visitor = $isTranscript ? $data['chat']['visitor'] : $data['visitor'];
            $email = User::normalizeEmail($visitor['email'] ?? null);
            $name = trim((string) ($visitor['name'] ?? '')) ?: null;
            $time = Carbon::parse($data['time'])->utc();

            $conversation = CustomerConversation::query()->firstOrCreate([
                'channel' => 'tawk',
                'property_id' => $data['property']['id'],
                'external_id' => $chatId,
            ]);
            $conversation = CustomerConversation::query()->whereKey($conversation->id)->lockForUpdate()->firstOrFail();
            $resolvedUserId = $this->identity->resolve($email, null)?->id;
            $identityConflictReason = null;
            if ($conversation->visitor_email !== null && $email !== $conversation->visitor_email) {
                $identityConflictReason = $email === null ? 'visitor_email_missing' : 'visitor_email_changed';
            } elseif ($resolvedUserId !== null && $conversation->user_id !== null && (int) $conversation->user_id !== $resolvedUserId) {
                $identityConflictReason = 'customer_link_mismatch';
            }
            $identityConflict = $identityConflictReason !== null;
            $originalConversationId = null;
            if ($identityConflict) {
                $originalConversationId = $conversation->id;
                $conversation = CustomerConversation::query()->firstOrCreate([
                    'channel' => 'tawk',
                    'property_id' => $data['property']['id'],
                    'external_id' => 'quarantine:'.hash('sha256', json_encode([
                        $data['property']['id'], $chatId, $email,
                    ], JSON_THROW_ON_ERROR)),
                ], [
                    'user_id' => null,
                    'visitor_name' => $name,
                    'visitor_email' => $email,
                ]);
                $conversation = CustomerConversation::query()->whereKey($conversation->id)->lockForUpdate()->firstOrFail();
            }
            $userId = $identityConflict ? null : ($conversation->user_id ?? $resolvedUserId);
            $messages = $isTranscript ? $data['chat']['messages'] : [];

            $activity = $this->activities->record([
                'source' => 'tawk_webhook',
                'event_id' => $eventId,
                'event_type' => match ($data['event']) {
                    'chat:start' => 'tawk.chat_started',
                    'chat:end' => 'tawk.chat_ended',
                    default => 'tawk.transcript_received',
                },
                'entity_type' => 'customer_conversation',
                'entity_id' => (string) $conversation->id,
                'user_id' => $userId,
                'contact_name' => $name,
                'contact_email' => $email,
                'message' => $data['event'] === 'chat:start' ? ($data['message']['text'] ?? null) : null,
                'payload_hash' => $payloadHash,
                'occurred_at' => $time,
                'details' => [
                    'property_id' => $data['property']['id'],
                    'chat_id' => $chatId,
                    'contact_verified' => false,
                    'identity_conflict' => $identityConflict,
                    'identity_conflict_reason' => $identityConflictReason,
                    'original_conversation_id' => $originalConversationId,
                    'message_count' => count($messages),
                ],
            ]);
            if (!$activity->wasRecentlyCreated) {
                return false;
            }

            if (!$identityConflict) {
                $conversation->user_id ??= $resolvedUserId;
                $conversation->visitor_name ??= $name;
                $conversation->visitor_email ??= $email;
            }
            if ($data['event'] === 'chat:start'
                && ($conversation->started_at === null || $time->lt($conversation->started_at))) {
                $conversation->started_at = $time;
            }
            if ($data['event'] === 'chat:end') {
                if ($conversation->ended_at === null || $time->gt($conversation->ended_at)) {
                    $conversation->ended_at = $time;
                }
                $conversation->status = 'closed';
            }
            if ($isTranscript) {
                $conversation->status = 'closed';
                $occurrences = [];
                foreach ($messages as $message) {
                    $sentAt = Carbon::parse($message['time'])->utc();
                    $senderType = match ($message['sender']['t']) {
                        'a' => 'agent',
                        'v' => 'visitor',
                        default => 'system',
                    };
                    $senderName = $message['sender']['n'] ?? null;
                    $content = (string) ($message['msg'] ?? '');
                    foreach ($message['attchs'] ?? [] as $attachment) {
                        $filename = $attachment['content']['file']['name'] ?? 'file';
                        $content .= ($content !== '' ? "\n" : '').'[Attachment: '.$filename.']';
                    }
                    if ($content === '') {
                        $content = '['.$message['type'].']';
                    }
                    $digest = hash('sha256', json_encode([
                        $senderType, $senderName, $content, $sentAt->toISOString(),
                    ], JSON_THROW_ON_ERROR));
                    $occurrences[$digest] = ($occurrences[$digest] ?? 0) + 1;
                    CustomerMessage::query()->firstOrCreate([
                        'conversation_id' => $conversation->id,
                        'fingerprint' => hash('sha256', $digest.':'.$occurrences[$digest]),
                    ], [
                        'sender_type' => $senderType,
                        'sender_name' => $senderName,
                        'content' => $content,
                        'sent_at' => $sentAt,
                    ]);
                    if ($conversation->started_at === null || $sentAt->lt($conversation->started_at)) {
                        $conversation->started_at = $sentAt;
                    }
                }
            }
            $conversation->save();

            return true;
        }, 3);
    }
}
