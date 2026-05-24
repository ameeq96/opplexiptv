(() => {
    "use strict";

    const DISCOUNTS = [5, 10, 15, 20, 25];
    const COLORS = ["#fff5f5", "#ffe1e1", "#ffc9c9", "#ffacac", "#df0303"];
    const AUTO_SHOW_DELAY = 8000;
    const DISMISS_DELAY = 12 * 60 * 60 * 1000;
    const $ = (selector, root = document) => root.querySelector(selector);

    const storage = {
        get nSpins() {
            return Number(localStorage.getItem("dw.spins")) || 0;
        },
        set nSpins(value) {
            localStorage.setItem("dw.spins", String(value));
        },
        get claimed() {
            return localStorage.getItem("dw.claimed");
        },
        set claimed(value) {
            localStorage.setItem("dw.claimed", value);
        },
        get closedAt() {
            return Number(localStorage.getItem("dw.closedAt")) || 0;
        },
        set closedAt(value) {
            localStorage.setItem("dw.closedAt", String(value));
        },
    };

    function shouldAutoShow() {
        return !storage.claimed && Date.now() - storage.closedAt > DISMISS_DELAY;
    }

    function runWhenIdle(callback) {
        if ("requestIdleCallback" in window) {
            window.requestIdleCallback(callback, { timeout: 2000 });
            return;
        }

        window.setTimeout(callback, 0);
    }

    function scheduleAutoShow(show) {
        if (!shouldAutoShow()) return;

        const schedule = () => {
            window.setTimeout(() => {
                if (document.visibilityState === "visible" && shouldAutoShow()) {
                    runWhenIdle(show);
                }
            }, AUTO_SHOW_DELAY);
        };

        if (document.readyState === "complete") {
            schedule();
        } else {
            window.addEventListener("load", schedule, { once: true });
        }
    }

    function celebrate(duration = 4000) {
        const canvas = document.createElement("canvas");
        canvas.style.position = "fixed";
        canvas.style.inset = "0";
        canvas.style.pointerEvents = "none";
        canvas.style.zIndex = "100000";
        document.body.appendChild(canvas);

        const context = canvas.getContext("2d");
        let width;
        let height;

        const resize = () => {
            width = canvas.width = innerWidth;
            height = canvas.height = innerHeight;
        };

        resize();
        addEventListener("resize", resize);

        const count = Math.min(300, Math.floor((width * height) / 12000));
        const pieces = Array.from({ length: count }, () => ({
            x: Math.random() * width,
            y: -20 - Math.random() * height * 0.3,
            size: 5 + 7 * Math.random(),
            angle: Math.random() * Math.PI,
            velocity: 1.5 + 3.5 * Math.random(),
            wobble: 0.05 + 0.2 * Math.random(),
            color: COLORS[Math.floor(Math.random() * COLORS.length)],
        }));
        const startedAt = performance.now();

        const draw = (now) => {
            context.clearRect(0, 0, width, height);
            pieces.forEach((piece) => {
                piece.y += piece.velocity + 2 * Math.random();
                piece.x += 0.8 * Math.sin(piece.angle += piece.wobble);
                context.save();
                context.translate(piece.x, piece.y);
                context.rotate(2 * piece.angle);
                context.fillStyle = piece.color;
                context.fillRect(-piece.size / 2, -piece.size / 2, piece.size, piece.size);
                context.restore();
            });

            if (now - startedAt < duration) {
                requestAnimationFrame(draw);
            } else {
                document.body.removeChild(canvas);
                removeEventListener("resize", resize);
            }
        };

        requestAnimationFrame(draw);
    }

    function drawWheel(canvas) {
        const context = canvas.getContext("2d");
        const width = canvas.width;
        const height = canvas.height;
        const centerX = width / 2;
        const centerY = height / 2;
        const radius = Math.min(width, height) / 2 - 4;
        const slice = (2 * Math.PI) / DISCOUNTS.length;

        context.clearRect(0, 0, width, height);

        DISCOUNTS.forEach((discount, index) => {
            const start = index * slice;
            const end = start + slice;

            context.beginPath();
            context.moveTo(centerX, centerY);
            context.arc(centerX, centerY, radius, start, end);
            context.closePath();
            context.fillStyle = COLORS[index % COLORS.length];
            context.fill();

            context.save();
            context.translate(centerX, centerY);
            context.rotate(start + slice / 2);
            context.textAlign = "right";
            context.fillStyle = "#111";
            context.font = "bold 22px system-ui,-apple-system,Segoe UI,Roboto,Arial";
            context.fillText(discount + "%", radius - 16, 8);
            context.restore();
        });

        context.beginPath();
        context.arc(centerX, centerY, 50, 0, 2 * Math.PI);
        context.fillStyle = "#fff";
        context.fill();
        context.lineWidth = 2;
        context.strokeStyle = "#111";
        context.stroke();
        context.fillStyle = "#111";
        context.font = "bold 14px system-ui,-apple-system,Segoe UI,Roboto,Arial";
        context.textAlign = "center";
        context.fillText((window.DW_I18N?.spin_center || "SPIN").toUpperCase(), centerX, centerY + 5);
    }

    function pickDiscount(spins) {
        const weights = spins >= 2 ? [78, 22] : [65, 35];
        const roll = Math.random() * (weights[0] + weights[1]);
        return roll < weights[0] ? 0 : 1;
    }

    function init() {
        const overlay = $("#dw-overlay");
        if (!overlay) return;

        const canvas = $("#dw-canvas", overlay);
        const spin = $("#dw-spin", overlay);
        const close = overlay.querySelector(".dw-close");
        const result = $("#dw-result", overlay);
        const resultValue = $("#dw-result-value", overlay);
        const copy = $("#dw-copy", overlay);
        const note = $("#dw-note", overlay);

        if (!canvas || !spin || !close || !result || !resultValue || !copy || !note) return;

        drawWheel(canvas);

        let spinning = false;
        const sliceDegrees = 360 / DISCOUNTS.length;

        function show() {
            overlay.classList.add("show");
            overlay.setAttribute("aria-hidden", "false");
        }

        function hide() {
            overlay.classList.remove("show");
            overlay.setAttribute("aria-hidden", "true");
            storage.closedAt = Date.now();
        }

        window.DiscountWheel = { show, hide };
        scheduleAutoShow(show);

        close.addEventListener("click", hide);
        overlay.addEventListener("click", (event) => {
            if (event.target === overlay) hide();
        });

        spin.addEventListener("click", () => {
            if (spinning) return;

            spin.hidden = true;
            const text = window.DW_I18N || {};

            if (storage.claimed) {
                note.textContent = (text.already_unlocked || "Discount already unlocked: :value%").replace(":value", storage.claimed);
                resultValue.textContent = storage.claimed + "%";
                result.hidden = false;
                celebrate(2500);
                return;
            }

            spinning = true;
            note.textContent = text.good_luck || "Good luck!";
            spin.disabled = true;

            const pickedIndex = pickDiscount(storage.nSpins);
            const discount = DISCOUNTS[pickedIndex];
            const spinTurns = 4 + Math.floor(2 * Math.random());
            const rotation = 360 * spinTurns + (270 - (pickedIndex + 0.5) * sliceDegrees);

            canvas.style.transform = `rotate(${rotation}deg)`;

            window.setTimeout(() => {
                storage.nSpins = storage.nSpins + 1;
                resultValue.textContent = discount + "%";
                result.hidden = false;
                storage.claimed = String(discount);
                note.textContent = text.congrats || "Congratulations! Your discount is ready.";
                celebrate(3800);
                spinning = false;
                spin.disabled = true;
            }, 6100);
        });

        copy.addEventListener("click", () => {
            const discount = storage.claimed || "5";
            const phone = copy.dataset.waPhone || "16393903194";
            const template = copy.dataset.waTemplate || "Hello, I got :discount% discount. Can you activate my subscription?";
            const message = template.replace(":discount", discount);
            const url = `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;

            if (typeof window.DiscountWheel?.hide === "function") {
                window.DiscountWheel.hide();
            } else {
                overlay.classList.remove("show");
                overlay.setAttribute("aria-hidden", "true");
                storage.closedAt = Date.now();
            }

            window.setTimeout(() => {
                window.location.href = url;
            }, 120);
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init, { once: true });
    } else {
        init();
    }
})();
