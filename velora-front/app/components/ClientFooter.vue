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
                    <a href="#" class="social-btn" aria-label="Instagram">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5" />
                            <circle cx="12" cy="12" r="4" />
                            <circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none" />
                        </svg>
                    </a>
                    <a href="#" class="social-btn" aria-label="Facebook">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
                        </svg>
                    </a>
                    <a href="#" class="social-btn" aria-label="X / Twitter">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4l16 16M4 20L20 4" />
                        </svg>
                    </a>
                    <a href="#" class="social-btn" aria-label="TikTok">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5" />
                        </svg>
                    </a>
                    <a href="#" class="social-btn" aria-label="Pinterest">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M12 2C6.48 2 2 6.48 2 12c0 4.24 2.65 7.86 6.39 9.29-.09-.78-.17-1.98.04-2.83.18-.76 1.23-5.22 1.23-5.22s-.31-.63-.31-1.57c0-1.47.85-2.57 1.91-2.57.9 0 1.34.68 1.34 1.49 0 .91-.58 2.27-.88 3.53-.25 1.05.52 1.91 1.55 1.91 1.86 0 3.11-2.39 3.11-5.22 0-2.15-1.45-3.76-4.08-3.76-2.98 0-4.83 2.23-4.83 4.73 0 .86.25 1.46.64 1.93.18.22.21.3.14.55-.05.18-.15.62-.2.79-.07.25-.27.34-.5.25-1.4-.57-2.05-2.12-2.05-3.84 0-3 2.57-6.63 7.69-6.63 4.11 0 6.83 2.99 6.83 6.2 0 4.25-2.36 7.43-5.85 7.43-1.17 0-2.28-.63-2.66-1.34l-.72 2.76c-.26 1-.97 2.24-1.45 3 1.09.33 2.25.51 3.44.51 5.52 0 10-4.48 10-10S17.52 2 12 2z" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- ── Nav columns ── -->
            <nav class="footer-nav-group">
                <div class="footer-col">
                    <h4 class="col-heading">Explore</h4>
                    <ul class="col-links">
                        <li>
                            <NuxtLink to="/" class="col-link">Home</NuxtLink>
                        </li>
                        <li>
                            <NuxtLink to="/menu" class="col-link">Menu</NuxtLink>
                        </li>
                        <li>
                            <NuxtLink to="/categories" class="col-link">Categories</NuxtLink>
                        </li>
                        <li>
                            <NuxtLink to="/bag" class="col-link">My Bag</NuxtLink>
                        </li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4 class="col-heading">Visit</h4>
                    <ul class="col-links">
                        <li class="col-info">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                            <span>12 Serene Ave, Istanbul</span>
                        </li>
                        <li class="col-info">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                            <span>Mon–Fri 07:00–22:00</span>
                        </li>
                        <li class="col-info">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                            <span>Sat–Sun 08:00–23:00</span>
                        </li>
                        <li class="col-info">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.38 2 2 0 0 1 3.58 1h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.5a16 16 0 0 0 5.59 5.59l.96-.96a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z" />
                            </svg>
                            <span>+90 212 000 00 00</span>
                        </li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4 class="col-heading">Stay in touch</h4>
                    <p class="newsletter-desc">Receive seasonal menus and quiet updates.</p>
                    <div class="newsletter-form">
                        <input v-model="email" type="email" placeholder="your@email.com" class="newsletter-input"
                            @keydown.enter="subscribe" />
                        <button class="newsletter-btn" @click="subscribe" :disabled="subscribed">
                            <span v-if="!subscribed">→</span>
                            <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                        </button>
                    </div>
                    <Transition name="fade">
                        <p v-if="subscribed" class="subscribed-msg">Thank you — you're on the list.</p>
                    </Transition>
                </div>
            </nav>

        </div>

        <!-- ── Copyright bar ── -->
        <div class="footer-bottom">
            <p class="copyright">
                &copy; {{ currentYear }} Velora Café. All rights reserved.
            </p>
            <div class="legal-links">
                <a href="#" class="legal-link">Privacy Policy</a>
                <span class="legal-dot">·</span>
                <a href="#" class="legal-link">Terms of Use</a>
                <span class="legal-dot">·</span>
                <a href="#" class="legal-link">Cookie Policy</a>
            </div>
        </div>

    </footer>
</template>

<script setup>
import { ref, computed } from 'vue'

const email = ref('')
const subscribed = ref(false)

const currentYear = computed(() => new Date().getFullYear())

function subscribe() {
    if (!email.value.trim() || subscribed.value) return
    // TODO: api('/newsletter', { method: 'POST', body: { email: email.value } })
    subscribed.value = true
    email.value = ''
}
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

/* ── Newsletter ── */
.newsletter-desc {
    font-size: 0.75rem;
    color: #7a6a58;
    line-height: 1.6;
    margin: 0;
}

.newsletter-form {
    display: flex;
    border: 1px solid rgba(201, 169, 110, 0.25);
    border-radius: 3px;
    overflow: hidden;
}

.newsletter-input {
    flex: 1;
    background: rgba(255, 255, 255, 0.04);
    border: none;
    outline: none;
    padding: 0.55rem 0.85rem;
    font-family: 'Lato', sans-serif;
    font-size: 0.78rem;
    color: #F5F0E8;
    min-width: 0;
}

.newsletter-input::placeholder {
    color: #6a5a48;
}

.newsletter-input:focus {
    background: rgba(255, 255, 255, 0.07);
}

.newsletter-btn {
    width: 40px;
    border: none;
    background: #C9A96E;
    color: #2C1A0E;
    font-size: 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.18s;
    flex-shrink: 0;
}

.newsletter-btn:hover:not(:disabled) {
    background: #b8924f;
}

.newsletter-btn:disabled {
    background: rgba(201, 169, 110, 0.4);
    cursor: default;
}

.subscribed-msg {
    font-size: 0.7rem;
    color: #C9A96E;
    letter-spacing: 0.06em;
    margin: 0;
}

/* ── Copyright bar ── */
.footer-bottom {
    border-top: 1px solid rgba(201, 169, 110, 0.1);
    padding: 1.1rem 3.5rem;
    max-width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}

.copyright {
    font-size: 0.68rem;
    color: #5a4a38;
    letter-spacing: 0.06em;
    margin: 0;
}

.legal-links {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.legal-link {
    font-size: 0.67rem;
    color: #5a4a38;
    text-decoration: none;
    letter-spacing: 0.05em;
    transition: color 0.15s;
}

.legal-link:hover {
    color: #C9A96E;
}

.legal-dot {
    color: #3a2a1a;
    font-size: 0.7rem;
}

/* ── Transition ── */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* ── Responsive ── */

/* Tablet */
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
        grid-template-columns: repeat(3, 1fr);
        gap: 1.75rem;
    }
}

/* Small tablet: stack brand + nav */
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
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
    }
}

/* Mobile */
@media (max-width: 600px) {
    .footer-nav-group {
        grid-template-columns: 1fr 1fr;
        gap: 1.75rem;
    }

    /* Newsletter column spans full width */
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
        flex-direction: column;
        align-items: flex-start;
        padding: 1rem 1.5rem;
        gap: 0.5rem;
    }
}

/* Very small */
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