<template>
    <footer class="velora-footer">

        <!-- Decorative top border -->
        <div class="footer-rule">
            <span class="rule-line" />
            <svg class="rule-icon" width="18" height="18" viewBox="0 0 24 24" fill="none">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z" fill="#C9A96E"
                    opacity="0.25" />
                <circle cx="12" cy="12" r="3" fill="#C9A96E" />
            </svg>
            <span class="rule-line" />
        </div>

        <div class="footer-inner">

            <!-- ── Brand column ── -->
            <div class="footer-brand">
                <NuxtLink to="/" class="brand-logo-wrap">
                    <img src="/logo.png" alt="Velora Café" class="brand-logo" />
                    <div class="brand-text">
                        <span class="brand-name">Velora</span>
                        <span class="brand-tagline">Taste the calm</span>
                    </div>
                </NuxtLink>
                <p class="brand-desc">
                    A sanctuary of flavour and stillness. Every cup crafted with intention, every visit a quiet
                    ritual.
                </p>

                <!-- Social icons -->
                <div class="social-row">
                    <a class="social-btn" aria-label="Instagram">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5" />
                            <circle cx="12" cy="12" r="4" />
                            <circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none" />
                        </svg>
                    </a>
                    <a class="social-btn" aria-label="TikTok">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5" />
                        </svg>
                    </a>
                    <a class="social-btn" aria-label="Youtube">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                            <path
                                d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- ── Nav columns ── -->
            <nav class="footer-nav-group">

                <!-- Explore: Home, Menu, Categories only -->
                <div class="footer-col">
                    <h4 class="col-heading">Explore</h4>
                    <ul class="col-links">
                        <li>
                            <NuxtLink to="/" class="col-link">Home</NuxtLink>
                        </li>
                        <li>
                            <NuxtLink to="/categories" class="col-link">Categories</NuxtLink>
                        </li>
                    </ul>
                </div>

                <!-- Visit: address, single work time, phone -->
                <div class="footer-col">
                    <h4 class="col-heading">Visit</h4>
                    <ul class="col-links">
                        <li class="col-info">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                            <span>Ashgabat</span>
                        </li>
                        <li class="col-info">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                            <span>Every day 07:00–23:00</span>
                        </li>
                        <li class="col-info">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.38 2 2 0 0 1 3.58 1h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.5a16 16 0 0 0 5.59 5.59l.96-.96a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z" />
                            </svg>
                            <span>+993 63 846663</span>
                        </li>
                    </ul>
                </div>

                <!-- Get the App: App Store + Google Play -->
                <div class="footer-col">
                    <h4 class="col-heading">Get the App</h4>
                    <p class="app-desc">Order from anywhere. Download the Velora app.</p>
                    <div class="app-btns">
                        <!-- App Store -->
                        <a class="app-btn" aria-label="Download on the App Store">
                            <i class="bi bi-apple store-icon-bi"></i>
                            <div class="app-btn-text">
                                <span class="app-btn-sub">Download on the</span>
                                <span class="app-btn-name">App Store</span>
                            </div>
                        </a>
                        <!-- Google Play -->
                        <a class="app-btn" aria-label="Get it on Google Play">
                            <i class="bi bi-google-play store-icon-bi"></i>
                            <div class="app-btn-text">
                                <span class="app-btn-sub">Get it on</span>
                                <span class="app-btn-name">Google Play</span>
                            </div>
                        </a>
                    </div>
                </div>

            </nav>
        </div>

        <!-- ── Copyright bar: centered, white ── -->
        <div class="footer-bottom">
            <p class="copyright">
                &copy; {{ currentYear }} Velora Café. All rights reserved.
            </p>
        </div>

    </footer>
</template>

<script setup>
import { computed } from 'vue'

useHead({
    link: [
        {
            rel: 'stylesheet',
            href: 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
        },
    ],
})

const currentYear = computed(() => new Date().getFullYear())
</script>

<style scoped>
/* ── Footer shell ── */
.velora-footer {
    background: #2C1A0E;
    color: #D4C5A9;
    font-family: 'Lato', sans-serif;
    margin-top: auto;
}

/* ── Decorative rule ── */
.footer-rule {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0 3.5rem;
    padding-top: 3rem;
}

.rule-line {
    flex: 1;
    height: 1px;
    background: linear-gradient(to right, transparent, rgba(201, 169, 110, 0.35), transparent);
}

.rule-icon {
    flex-shrink: 0;
    opacity: 0.8;
}

/* ── Inner grid ── */
.footer-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2.5rem 3.5rem 3rem;
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 4rem;
    align-items: start;
}

/* ── Brand column ── */
.brand-logo-wrap {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    text-decoration: none;
    margin-bottom: 1rem;
}

.brand-logo {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid rgba(201, 169, 110, 0.25);
}

.brand-text {
    display: flex;
    flex-direction: column;
    line-height: 1;
    gap: 4px;
}

.brand-name {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 1.4rem;
    font-weight: 400;
    color: #F5F0E8;
    letter-spacing: 0.02em;
}

.brand-tagline {
    font-size: 0.58rem;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: #C9A96E;
}

.brand-desc {
    font-size: 0.78rem;
    line-height: 1.75;
    color: #9a8a78;
    margin: 0 0 1.5rem;
    max-width: 240px;
}

/* Social icons */
.social-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.social-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border: 1px solid rgba(201, 169, 110, 0.2);
    border-radius: 50%;
    color: #9a8a78;
    text-decoration: none;
    transition: border-color 0.2s, color 0.2s, background 0.2s;
}

.social-btn:hover {
    border-color: #C9A96E;
    color: #C9A96E;
    background: rgba(201, 169, 110, 0.08);
}

/* ── Nav group ── */
.footer-nav-group {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2.5rem;
}

.footer-col {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.col-heading {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 0.95rem;
    font-weight: 400;
    color: #F5F0E8;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin: 0 0 0.25rem;
    padding-bottom: 0.65rem;
    border-bottom: 1px solid rgba(201, 169, 110, 0.15);
}

.col-links {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 0.55rem;
}

.col-link {
    font-size: 0.78rem;
    color: #9a8a78;
    text-decoration: none;
    letter-spacing: 0.04em;
    transition: color 0.18s;
    display: inline-block;
}

.col-link:hover {
    color: #C9A96E;
}

.col-info {
    display: flex;
    align-items: flex-start;
    gap: 0.55rem;
    font-size: 0.76rem;
    color: #9a8a78;
    line-height: 1.5;
}

.col-info svg {
    flex-shrink: 0;
    margin-top: 2px;
    color: #C9A96E;
    opacity: 0.7;
}

/* ── App Store / Google Play buttons ── */
.app-desc {
    font-size: 0.75rem;
    color: #7a6a58;
    line-height: 1.6;
    margin: 0;
}

.app-btns {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
}

.app-btn {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.6rem 0.9rem;
    border: 1px solid rgba(201, 169, 110, 0.25);
    border-radius: 8px;
    text-decoration: none;
    color: #F5F0E8;
    background: rgba(255, 255, 255, 0.04);
    transition: border-color 0.2s, background 0.2s;
}

.app-btn:hover {
    border-color: #C9A96E;
    background: rgba(201, 169, 110, 0.08);
}

.app-btn svg {
    flex-shrink: 0;
    opacity: 0.9;
}

.store-icon {
    flex-shrink: 0;
}

.store-icon-bi {
    font-size: 1.4rem;
    flex-shrink: 0;
    color: currentColor;
}

.app-btn-text {
    display: flex;
    flex-direction: column;
    gap: 1px;
}

.app-btn-sub {
    font-size: 0.58rem;
    color: #9a8a78;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

.app-btn-name {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 0.95rem;
    color: #F5F0E8;
    letter-spacing: 0.02em;
}

/* ── Copyright bar ── */
.footer-bottom {
    border-top: 1px solid rgba(201, 169, 110, 0.1);
    padding: 1.1rem 3.5rem;
    text-align: center;
}

.copyright {
    font-size: 0.68rem;
    color: #ffffff;
    letter-spacing: 0.06em;
    margin: 0;
}

/* ── Responsive ── */
@media (max-width: 1024px) {
    .footer-inner {
        grid-template-columns: 240px 1fr;
        gap: 3rem;
        padding: 2.5rem 2.5rem 3rem;
    }

    .footer-rule {
        padding: 0 2.5rem;
        padding-top: 3rem;
    }

    .footer-bottom {
        padding: 1rem 2.5rem;
    }

    .footer-nav-group {
        gap: 1.75rem;
    }
}

@media (max-width: 860px) {
    .footer-inner {
        grid-template-columns: 1fr;
        gap: 2.5rem;
        padding: 2rem 2rem 2.5rem;
    }

    .footer-rule {
        padding: 0 2rem;
        padding-top: 2.5rem;
    }

    .footer-bottom {
        padding: 1rem 2rem;
    }

    .brand-desc {
        max-width: 100%;
    }

    .footer-nav-group {
        gap: 1.5rem;
    }
}

@media (max-width: 600px) {
    .footer-nav-group {
        grid-template-columns: 1fr 1fr;
        gap: 1.75rem;
    }

    .footer-col:last-child {
        grid-column: 1 / -1;
    }

    .footer-inner {
        padding: 1.75rem 1.5rem 2rem;
    }

    .footer-rule {
        padding: 0 1.5rem;
        padding-top: 2rem;
    }

    .footer-bottom {
        padding: 1rem 1.5rem;
    }
}

@media (max-width: 380px) {
    .footer-nav-group {
        grid-template-columns: 1fr;
    }

    .footer-col:last-child {
        grid-column: auto;
    }

    .footer-inner {
        padding: 1.5rem 1.25rem 2rem;
    }

    .footer-rule {
        padding: 0 1.25rem;
        padding-top: 2rem;
    }

    .footer-bottom {
        padding: 1rem 1.25rem;
    }
}
</style>