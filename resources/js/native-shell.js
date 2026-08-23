const configElement = document.querySelector('template[data-native-shell-config]');

if (configElement) {
    const config = {
        menuToggleLabel: configElement.dataset.menuToggleLabel
            || document.querySelector('.mobile-nav-toggler')?.getAttribute('aria-label')
            || '',
        scrollBehavior: configElement.dataset.scrollBehavior || 'instant',
        initialScrollUpdate: configElement.dataset.initialScrollUpdate || 'animation-frame',
        manageDropdownDisplay: configElement.dataset.manageDropdownDisplay !== 'false',
        enrichFaqAria: configElement.dataset.enrichFaqAria !== 'false',
    };

    function directChildByTag(parent, tagName) {
        if (!parent) return null;
        const expectedTag = tagName.toUpperCase();

        return Array.from(parent.children).find((child) => child.tagName === expectedTag) || null;
    }

    function initScrollUi() {
        const header = document.querySelector('.main-header');
        const scrollButton = document.querySelector('.scroll-to-target');
        let scheduled = false;

        const update = () => {
            const isPastHeader = window.scrollY >= 300;
            if (header) header.classList.toggle('fixed-header', isPastHeader);
            if (scrollButton) scrollButton.style.display = isPastHeader ? 'block' : 'none';
            scheduled = false;
        };

        const scheduleUpdate = () => {
            if (scheduled) return;
            scheduled = true;
            window.requestAnimationFrame(update);
        };

        window.addEventListener('scroll', scheduleUpdate, { passive: true });

        if (scrollButton) {
            scrollButton.addEventListener('click', () => {
                if (config.scrollBehavior === 'smooth') {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }

                window.scrollTo(0, 0);
            });
        }

        if (config.initialScrollUpdate === 'immediate') {
            update();
        } else {
            scheduleUpdate();
        }
    }

    function initMobileMenu() {
        const source = document.querySelector('.main-header .main-menu .navigation');
        const target = document.querySelector('.mobile-menu .menu-outer');

        if (source && target && !target.querySelector('.navigation')) {
            target.insertBefore(source.cloneNode(true), target.firstChild);
        }

        document.querySelectorAll('.mobile-menu .navigation li.dropdown').forEach((item) => {
            const submenu = directChildByTag(item, 'ul');
            if (!submenu || item.querySelector(':scope > .dropdown-btn')) return;

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'dropdown-btn';
            button.setAttribute('aria-label', config.menuToggleLabel);
            button.setAttribute('aria-expanded', 'false');
            button.innerHTML = '<span class="fa fa-angle-down" aria-hidden="true"></span>';
            item.appendChild(button);

            const toggle = (event) => {
                if (event) event.preventDefault();
                const willOpen = submenu.style.display !== 'block';
                submenu.style.display = willOpen ? 'block' : 'none';
                button.classList.toggle('open', willOpen);
                button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            };

            button.addEventListener('click', toggle);

            const link = directChildByTag(item, 'a');
            if (link && (link.getAttribute('href') === '#' || link.getAttribute('href') === '')) {
                link.addEventListener('click', toggle);
            }
        });

        const openButton = document.querySelector('.mobile-nav-toggler');
        const backdrop = document.querySelector('.mobile-menu .menu-backdrop');
        const closeButton = document.querySelector('.mobile-menu .close-btn');
        const closeMenu = () => document.body.classList.remove('mobile-menu-visible');

        if (openButton) {
            openButton.addEventListener('click', () => {
                document.body.classList.add('mobile-menu-visible');
            });
        }

        if (backdrop) backdrop.addEventListener('click', closeMenu);
        if (closeButton) closeButton.addEventListener('click', closeMenu);

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeMenu();
        });
    }

    function initDropdowns() {
        const dropdowns = document.querySelectorAll('.main-header .main-menu .navigation > li.dropdown');

        const closeDropdowns = (except) => {
            dropdowns.forEach((item) => {
                if (item === except) return;
                item.classList.remove('native-dropdown-open');
                const submenu = directChildByTag(item, 'ul');
                if (submenu) {
                    if (config.manageDropdownDisplay) {
                        submenu.style.removeProperty('display');
                    }
                    submenu.style.removeProperty('transform');
                    submenu.style.removeProperty('opacity');
                    submenu.style.removeProperty('visibility');
                }
                const link = directChildByTag(item, 'a');
                if (link) link.setAttribute('aria-expanded', 'false');
            });
        };

        dropdowns.forEach((item) => {
            const link = directChildByTag(item, 'a');
            const submenu = directChildByTag(item, 'ul');
            if (!link || !submenu || !['', '#'].includes(link.getAttribute('href') || '')) return;

            link.addEventListener('click', (event) => {
                event.preventDefault();
                const willOpen = !item.classList.contains('native-dropdown-open');
                closeDropdowns(item);
                item.classList.toggle('native-dropdown-open', willOpen);
                if (config.manageDropdownDisplay) {
                    submenu.style.display = willOpen ? 'block' : '';
                }
                submenu.style.transform = willOpen ? 'scaleY(1)' : '';
                submenu.style.opacity = willOpen ? '1' : '';
                submenu.style.visibility = willOpen ? 'visible' : '';
                link.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            });
        });

        document.addEventListener('click', (event) => {
            if (!event.target.closest('.main-header .main-menu .navigation > li.dropdown')) {
                closeDropdowns();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeDropdowns();
        });
    }

    function initFaqs() {
        document.querySelectorAll('.accordion-box').forEach((accordion) => {
            const buttons = accordion.querySelectorAll('.acc-btn');

            buttons.forEach((button) => {
                const item = button.closest('.accordion');
                const content = button.nextElementSibling;
                if (!item || !content) return;

                const isInitiallyOpen = content.classList.contains('current');
                button.tabIndex = 0;
                if (config.enrichFaqAria) {
                    button.setAttribute('role', 'button');
                    button.setAttribute('aria-expanded', isInitiallyOpen ? 'true' : 'false');
                }
                content.hidden = !isInitiallyOpen;

                const openItem = () => {
                    if (button.classList.contains('active') && !content.hidden) return;

                    accordion.querySelectorAll('.accordion').forEach((otherItem) => {
                        const otherButton = otherItem.querySelector(':scope > .acc-btn');
                        const otherContent = otherButton ? otherButton.nextElementSibling : null;
                        otherItem.classList.remove('active-block');

                        if (otherButton) {
                            otherButton.classList.remove('active');
                            otherButton.setAttribute('aria-expanded', 'false');
                        }

                        if (otherContent) {
                            otherContent.classList.remove('current');
                            otherContent.hidden = true;
                        }
                    });

                    item.classList.add('active-block');
                    button.classList.add('active');
                    button.setAttribute('aria-expanded', 'true');
                    content.classList.add('current');
                    content.hidden = false;
                };

                button.addEventListener('click', openItem);
                button.addEventListener('keydown', (event) => {
                    if (event.key !== 'Enter' && event.key !== ' ') return;
                    event.preventDefault();
                    openItem();
                });
            });
        });
    }

    function initNativeShell() {
        initScrollUi();
        initMobileMenu();
        initDropdowns();
        initFaqs();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNativeShell, { once: true });
    } else {
        initNativeShell();
    }
}
