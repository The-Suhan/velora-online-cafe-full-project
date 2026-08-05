// composables/useNotifySound.ts
//
// Bildirim sesi. Ses dosyası yerine WebAudio ile kısa bir bip sentezlenir —
// sıfır asset, sıfır ağ isteği, tema/dil bağımsız.
//
// Tarayıcılar kullanıcı bir kez sayfayla etkileşime girmeden ses çalmayı
// engeller, bu yüzden ilk pointerdown'da sessizce "kilit açılır".

type SoundKind = 'status-change' | 'new-order'

// Modül düzeyinde tekil AudioContext — her bip için yenisini açmak pahalı ve
// tarayıcı context sayısını sınırlıyor.
let ctx: AudioContext | null = null

function getContext(): AudioContext | null {
    if (!import.meta.client) return null
    if (ctx) return ctx

    const Ctor = window.AudioContext ?? (window as any).webkitAudioContext
    if (!Ctor) return null

    try {
        ctx = new Ctor()
        return ctx
    } catch {
        return null
    }
}

/** Tek bir nota: kısa attack/decay zarfı ile sinüs. */
function beep(context: AudioContext, freq: number, startAt: number, duration = 0.14) {
    const osc = context.createOscillator()
    const gain = context.createGain()

    osc.type = 'sine'
    osc.frequency.setValueAtTime(freq, startAt)

    // Sert başlangıç/bitiş "klik" sesi çıkarır, bu yüzden rampalanır.
    gain.gain.setValueAtTime(0.0001, startAt)
    gain.gain.exponentialRampToValueAtTime(0.22, startAt + 0.015)
    gain.gain.exponentialRampToValueAtTime(0.0001, startAt + duration)

    osc.connect(gain).connect(context.destination)
    osc.start(startAt)
    osc.stop(startAt + duration + 0.02)
}

export const useNotifySound = () => {
    const unlocked = useState('sound:unlocked', () => false)

    /** İlk kullanıcı etkileşiminde sesi etkinleştir. Idempotent. */
    function armUnlock() {
        if (!import.meta.client || unlocked.value) return

        const handler = () => {
            unlocked.value = true
            // Bazı tarayıcılar context'i "suspended" başlatır.
            getContext()?.resume().catch(() => { })
        }

        document.addEventListener('pointerdown', handler, { once: true })
        document.addEventListener('keydown', handler, { once: true })
    }

    function play(kind: SoundKind) {
        if (!unlocked.value) return

        const context = getContext()
        if (!context) return

        try {
            if (context.state === 'suspended') context.resume().catch(() => { })

            const now = context.currentTime
            if (kind === 'new-order') {
                // İki notalı yükselen ikili bip — admin için daha dikkat çekici.
                beep(context, 660, now)
                beep(context, 880, now + 0.16)
            } else {
                beep(context, 720, now)
            }
        } catch {
            // Ses asla akışı bozmamalı.
        }
    }

    return { play, armUnlock, unlocked }
}
