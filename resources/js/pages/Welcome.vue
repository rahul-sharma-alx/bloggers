<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import type { Post } from '@/types'

type Props = {
    posts: Post[]
}

const props = defineProps<Props>()
const currentIndex = ref(0)
let carouselTimer: ReturnType<typeof setInterval> | null = null

const tailwindConfig = {
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                'primary-container': '#1b1c1c', 'surface-container-lowest': '#ffffff', 'tertiary-fixed': '#dfe3e0',
                'surface-tint': '#5e5e5e', 'primary-fixed-dim': '#c7c6c6', 'on-secondary-container': '#4b6861',
                'outline-variant': '#c4c7c7', 'error-container': '#ffdad6', 'inverse-on-surface': '#f1f1ef',
                'tertiary-container': '#181d1b', 'on-error': '#ffffff', 'on-secondary': '#ffffff',
                surface: '#faf9f7', 'on-primary': '#ffffff', 'on-tertiary': '#ffffff',
                'surface-container': '#efeeec', 'secondary-fixed-dim': '#adcdc4', 'on-primary-fixed-variant': '#464747',
                'inverse-surface': '#2f3130', 'on-secondary-fixed-variant': '#2f4c45', 'on-background': '#1a1c1b',
                secondary: '#47645d', 'surface-bright': '#faf9f7', 'on-error-container': '#93000a',
                'on-tertiary-container': '#808582', outline: '#747878', 'on-secondary-fixed': '#02201a',
                'surface-container-highest': '#e3e2e0', 'on-primary-container': '#848483', tertiary: '#000000',
                'secondary-container': '#c6e7dd', 'surface-variant': '#e3e2e0', 'secondary-fixed': '#c9e9e0',
                error: '#ba1a1a', 'on-tertiary-fixed': '#181d1b', 'inverse-primary': '#c7c6c6', primary: '#000000',
                background: '#faf9f7', 'surface-container-high': '#e9e8e6', 'primary-fixed': '#e3e2e2',
                'on-surface-variant': '#444748', 'surface-container-low': '#f4f3f1', 'tertiary-fixed-dim': '#c3c7c4',
                'on-surface': '#1a1c1b', 'surface-dim': '#dadad8', 'on-tertiary-fixed-variant': '#434846',
                'on-primary-fixed': '#1b1c1c',
            },
            borderRadius: { DEFAULT: '0.125rem', lg: '0.25rem', xl: '0.5rem', full: '0.75rem' },
            spacing: {
                'margin-mobile': '20px', 'container-max': '1120px', gutter: '24px',
                'stack-md': '32px', unit: '8px', 'reading-column': '720px', 'stack-lg': '64px', 'stack-sm': '16px',
            },
            fontFamily: {
                'headline-md': ['Newsreader'], 'body-lg': ['Hanken Grotesk'], 'display-lg': ['Newsreader'],
                'display-lg-mobile': ['Newsreader'], 'body-md': ['Hanken Grotesk'], 'label-md': ['Hanken Grotesk'],
                'label-sm': ['Hanken Grotesk'], 'headline-sm': ['Newsreader'],
            },
            fontSize: {
                'headline-md': ['32px', { lineHeight: '1.3', fontWeight: '600' }],
                'body-lg': ['20px', { lineHeight: '1.7', fontWeight: '400' }],
                'display-lg': ['48px', { lineHeight: '1.1', letterSpacing: '-0.02em', fontWeight: '700' }],
                'display-lg-mobile': ['36px', { lineHeight: '1.2', fontWeight: '700' }],
                'body-md': ['16px', { lineHeight: '1.6', fontWeight: '400' }],
                'label-md': ['14px', { lineHeight: '1.2', letterSpacing: '0.05em', fontWeight: '500' }],
                'label-sm': ['12px', { lineHeight: '1.2', fontWeight: '500' }],
                'headline-sm': ['24px', { lineHeight: '1.4', fontWeight: '600' }],
            },
        },
    },
}

function heroImage(post: Post) {
    if (post.featured_image) return post.featured_image
    const m = post.body?.match(/<img[^>]+src="([^"]+)"/)
    return m?.[1] ?? null
}

function nextSlide() {
    currentIndex.value = (currentIndex.value + 1) % props.posts.length
}

onMounted(() => {
    if (props.posts.length > 1) {
        carouselTimer = setInterval(nextSlide, 5000)
    }
    if (!document.querySelector('[data-cdn-tailwind]')) {
        const s = document.createElement('script')
        s.src = 'https://cdn.tailwindcss.com?plugins=forms,container-queries'
        s.setAttribute('data-cdn-tailwind', '')
        s.onload = () => { (window as any).tailwind.config = tailwindConfig }
        document.head.appendChild(s)
    }
    const links = [
        ['preconnect', 'https://fonts.googleapis.com'],
        ['preconnect', 'https://fonts.gstatic.com', 'crossorigin'],
        ['stylesheet', 'https://fonts.googleapis.com/css2?family=Hanken+Grotesk:ital,wght@0,100..900;1,100..900&family=Newsreader:ital,opsz,wght@0,6..72,200..800;1,6..72,200..800&display=swap'],
        ['stylesheet', 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap']
    ]
    for (const [rel, href, ...extra] of links) {
        if (!document.querySelector(`link[href="${href}"]`)) {
            const l = document.createElement('link')
            l.rel = rel
            l.href = href
            if (extra.length) l.setAttribute(extra[0], '')
            document.head.appendChild(l)
        }
    }

    window.addEventListener('scroll', () => {
        const winScroll = document.body.scrollTop || document.documentElement.scrollTop
        const height = document.documentElement.scrollHeight - document.documentElement.clientHeight
        const scrolled = (winScroll / height) * 100
        const bar = document.getElementById('readingProgress')
        if (bar) bar.style.width = scrolled + '%'
        const header = document.querySelector('header')
        if (header) {
            header.classList.toggle('bg-white/80', window.scrollY > 50)
            header.classList.toggle('backdrop-blur-md', window.scrollY > 50)
            header.classList.toggle('shadow-sm', window.scrollY > 50)
        }
    })

    const c = document.getElementById('shader-canvas-ANIMATION_2') as HTMLCanvasElement | null
    if (!c) return
    const gl = c.getContext('webgl') || c.getContext('experimental-webgl')
    if (!gl) return
    function syncSize() {
        const w = c.clientWidth || 1280; const h = c.clientHeight || 720
        if (c.width !== w || c.height !== h) { c.width = w; c.height = h }
    }
    if (typeof ResizeObserver !== 'undefined') new ResizeObserver(syncSize).observe(c)
    syncSize()
    const vs = `attribute vec2 a_position;varying vec2 v_texCoord;void main(){v_texCoord=a_position*0.5+0.5;gl_Position=vec4(a_position,0.0,1.0);}`
    const fs = `precision highp float;varying vec2 v_texCoord;uniform float u_time;uniform vec2 u_resolution;void main(){vec2 uv=v_texCoord;float noise=sin(uv.x*3.0+u_time*0.2)*cos(uv.y*2.0+u_time*0.3);float noise2=sin(uv.y*4.0-u_time*0.15)*cos(uv.x*2.5+u_time*0.25);vec3 color1=vec3(0.98,0.977,0.968);vec3 color2=vec3(0.957,0.953,0.945);vec3 color=mix(color1,color2,(noise+noise2)*0.5+0.5);float vignette=1.0-length(uv-0.5)*0.4;color*=vignette;gl_FragColor=vec4(color,1.0);}`
    function cs(type: number, src: string) { const s = gl.createShader(type)!; gl.shaderSource(s, src); gl.compileShader(s); return s }
    const prog = gl.createProgram()!
    gl.attachShader(prog, cs(gl.VERTEX_SHADER, vs))
    gl.attachShader(prog, cs(gl.FRAGMENT_SHADER, fs))
    gl.linkProgram(prog)
    gl.useProgram(prog)
    const buf = gl.createBuffer()
    gl.bindBuffer(gl.ARRAY_BUFFER, buf)
    gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1,-1,1,-1,-1,1,1,1]), gl.STATIC_DRAW)
    const pos = gl.getAttribLocation(prog, 'a_position')
    gl.enableVertexAttribArray(pos)
    gl.vertexAttribPointer(pos, 2, gl.FLOAT, false, 0, 0)
    const uTime = gl.getUniformLocation(prog, 'u_time')
    const uRes = gl.getUniformLocation(prog, 'u_resolution')
    let mouse = { x: c.width / 2, y: c.height / 2 }
    window.addEventListener('mousemove', (event) => {
        const rect = c.getBoundingClientRect()
        if (rect.width && rect.height) {
            mouse.x = ((event.clientX - rect.left) / rect.width) * c.width
            mouse.y = (1.0 - (event.clientY - rect.top) / rect.height) * c.height
        }
    })
    function render(t: number) {
        if (typeof ResizeObserver === 'undefined') syncSize()
        gl.viewport(0, 0, c.width, c.height)
        if (uTime) gl.uniform1f(uTime, t * 0.001)
        if (uRes) gl.uniform2f(uRes, c.width, c.height)
        gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4)
        requestAnimationFrame(render)
    }
    render(0)
})

onUnmounted(() => {
    if (carouselTimer) clearInterval(carouselTimer)
})
</script>

<template>
    <div class="progress-bar" id="readingProgress"></div>

    <div class="fixed inset-0 -z-10 opacity-30 pointer-events-none">
        <div class="absolute inset-0 w-full h-full">
            <canvas id="shader-canvas-ANIMATION_2" style="display:block;width:100%;height:100%"></canvas>
        </div>
    </div>

    <header class="bg-background border-b border-outline-variant fixed top-0 left-0 w-full z-50 transition-all duration-300">
        <div class="flex justify-between items-center w-full px-margin-mobile md:px-gutter max-w-container-max mx-auto h-20">
            <div class="flex items-center gap-8">
                <a class="font-headline-md text-headline-md font-bold text-primary tracking-tight" href="/">Ink & Echo</a>
                <nav class="hidden md:flex gap-6 items-center">
                    <a class="font-body-md text-body-md text-on-surface-variant hover:text-primary transition-colors" href="#">Topics</a>
                    <a class="font-body-md text-body-md text-on-surface-variant hover:text-primary transition-colors" href="#">About</a>
                    <a class="font-body-md text-body-md text-on-surface-variant hover:text-primary transition-colors" href="#">Newsletter</a>
                </nav>
            </div>
            <div class="flex items-center gap-4">
                <a href="/login" class="bg-primary text-on-primary px-5 py-2 font-label-md transition-all duration-200 hover:opacity-85 hover:scale-[1.02] active:scale-[0.98] inline-block">Sign In</a>
            </div>
        </div>
    </header>

    <main class="pt-20">
        <section id="hero"
            v-if="posts.length"
            class="relative min-h-[90vh] flex items-center overflow-hidden border-b border-outline-variant"
        >
            <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/30 to-transparent z-10 pointer-events-none" />
            <div class="absolute inset-0 transition-opacity duration-700 ease-in-out" :key="currentIndex">
                <img
                    :src="heroImage(posts[currentIndex]) ?? ''"
                    alt=""
                    class="w-full h-full object-cover"
                    style=" filter:
                saturate(0.75)
                brightness(0.9);"
                />
                <div class="absolute inset-0 bg-white/40 pointer-events-none"></div>
            </div>
            <div class="relative z-20 max-w-container-max mx-auto px-margin-mobile md:px-gutter w-full py-stack-lg">
                <div
                    :key="'content-' + currentIndex"
                    v-motion
                    :initial="{ opacity: 0, y: 40 }"
                    :enter="{ opacity: 1, y: 0, transition: { type: 'spring', stiffness: 100, damping: 20 } }"
                    class="max-w-xl"
                >
                    <span class="inline-block bg-secondary/90 text-on-secondary px-3 py-1 font-label-sm uppercase tracking-[0.15em] mb-6">Featured Story</span>
                    <h1 class="font-display-lg-mobile md:font-display-lg text-display-lg-mobile md:text-display-lg text-primary mb-6 leading-[1.08]" style="text-shadow: 0 2px 12px rgba(0,0,0,0.15)">
                        {{ posts[currentIndex].title }}
                    </h1>
                    <p class="font-body-lg text-body-lg text-on-surface-variant mb-8 line-clamp-3 text-on-primary" style="text-shadow: 0 1px 6px rgba(0,0,0,0.12)" v-html="posts[currentIndex].body?.replace(/<[^>]+>/g, '').slice(0, 250)" />
                    <div class="flex items-center gap-5">
                        <Link
                            :href="`/articles/${posts[currentIndex].slug}`"
                            class="inline-flex items-center gap-2 bg-primary text-on-primary px-6 py-3 font-label-md transition-all duration-200 hover:opacity-85 hover:scale-[1.02] active:scale-[0.98]"
                        >
                            Read the Essay
                            <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                        </Link>
                        <span class="font-body-md text-body-md text-on-surface-variant">
                            {{ posts[currentIndex].user?.name ?? 'Anonymous' }} &middot; {{ new Date(posts[currentIndex].created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}
                        </span>
                    </div>
                </div>
                <nav v-if="posts.length > 1" class="flex gap-2 mt-8">
                    <button
                        v-for="(_, i) in posts"
                        :key="'dot-' + i"
                        @click="currentIndex = i; if (carouselTimer) { clearInterval(carouselTimer); carouselTimer = setInterval(nextSlide, 3000) }"
                        :class="['w-2 h-2 rounded-full transition-all duration-300', i === currentIndex ? 'bg-secondary w-6' : 'bg-outline-variant hover:bg-outline']"
                        :aria-label="'Go to slide ' + (i + 1)"
                    />
                </nav>
            </div>
        </section>

        <section v-if="posts.length" class="py-stack-lg">
            <div class="max-w-container-max mx-auto px-margin-mobile md:px-gutter">
                <div
                    v-motion
                    :initial="{ opacity: 0, y: 20 }"
                    :enter="{ opacity: 1, y: 0, transition: { delay: 200 } }"
                    class="flex justify-between items-end mb-stack-md pb-stack-sm"
                >
                    <h2 class="font-headline-md text-headline-md text-primary">Latest Articles</h2>
                    <span class="font-label-md text-on-surface-variant">{{ posts.length }} articles</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <article
                        v-for="(post, i) in posts"
                        :key="post.id"
                        v-motion
                        :initial="{ opacity: 0, y: 30 }"
                        :enter="{ opacity: 1, y: 0, transition: { type: 'spring', stiffness: 80, damping: 18, delay: 100 + i * 60 } }"
                        :hover="{ y: -4, transition: { type: 'spring', stiffness: 200, damping: 15 } }"
                        class="group flex flex-col bg-surface-container-lowest border border-outline-variant overflow-hidden transition-shadow duration-300 hover:shadow-lg"
                    >
                        <div v-if="heroImage(post)" class="h-48 overflow-hidden">
                            <img
                                :src="heroImage(post)!"
                                alt=""
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                            />
                        </div>
                        <div class="flex flex-col flex-1 p-6">
                            <div class="flex items-center gap-3 mb-3">
                                <span class="bg-secondary-container/60 text-secondary px-2 py-0.5 font-label-sm uppercase tracking-wider text-[11px]">{{ post.team?.name ?? 'Uncategorized' }}</span>
                                <span class="text-label-sm text-on-surface-variant">{{ new Date(post.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) }}</span>
                            </div>
                            <h3 class="font-headline-sm text-headline-sm text-primary mb-3 leading-snug group-hover:text-secondary transition-colors duration-300 line-clamp-2">{{ post.title }}</h3>
                            <p class="font-body-md text-body-md text-on-surface-variant mb-5 line-clamp-3 leading-relaxed" v-html="post.body?.replace(/<[^>]+>/g, '').slice(0, 200)" />
                            <div class="mt-auto flex items-center gap-2">
                                <Link
                                    :href="`/articles/${post.slug}`"
                                    class="font-label-md text-primary underline underline-offset-4 decoration-outline-variant hover:decoration-primary transition-all duration-200"
                                >
                                    Read More
                                </Link>
                                <span class="material-symbols-outlined text-primary text-[16px] transition-transform duration-200 group-hover:translate-x-1">arrow_forward</span>
                            </div>
                        </div>
                    </article>
                </div>

                <div v-if="posts.length > 6" class="mt-stack-md text-center">
                    <button class="inline-flex items-center gap-2 border border-outline-variant text-primary px-6 py-3 font-label-md transition-all duration-200 hover:bg-surface-container-low hover:scale-[1.02] active:scale-[0.98]">
                        View All Articles
                        <span class="material-symbols-outlined text-[18px]">expand_more</span>
                    </button>
                </div>
            </div>
        </section>

        <section v-else class="py-stack-lg">
            <div class="max-w-container-max mx-auto px-margin-mobile md:px-gutter text-center py-24">
                <p class="font-body-lg text-body-lg text-on-surface-variant">No articles yet. The first story is being written.</p>
            </div>
        </section>

        <section class="py-stack-lg bg-surface-container-low">
            <div class="max-w-reading-column mx-auto px-margin-mobile text-center">
                <div class="w-16 h-px bg-outline mx-auto mb-8"></div>
                <h2 class="font-headline-md text-headline-md text-primary mb-4">Subscribe to Ink & Echo</h2>
                <p class="font-body-lg text-body-lg text-on-surface-variant mb-10 max-w-md mx-auto">
                    Weekly letters on art, philosophy, and the quiet life. No noise, just depth.
                </p>
                <form class="flex flex-col sm:flex-row gap-3 items-center justify-center max-w-md mx-auto">
                    <input class="w-full bg-background border border-outline-variant px-4 py-3 font-body-md outline-none transition-colors focus:border-secondary placeholder:text-on-surface-variant/50" placeholder="Your email address" type="email" />
                    <button class="w-full sm:w-auto bg-primary text-on-primary px-8 py-3 font-label-md transition-all duration-200 hover:opacity-85 hover:scale-[1.02] active:scale-[0.98] whitespace-nowrap" type="submit">Join</button>
                </form>
            </div>
        </section>
    </main>

    <footer class="bg-background border-t border-outline-variant py-stack-lg">
        <div class="max-w-container-max mx-auto px-margin-mobile md:px-gutter flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex flex-col items-center md:items-start gap-2">
                <span class="font-headline-sm text-headline-sm text-primary tracking-tight">Ink & Echo</span>
                <p class="font-label-md text-label-md text-on-surface-variant opacity-70">&copy; {{ new Date().getFullYear() }} Ink & Echo. All rights reserved.</p>
            </div>
            <nav class="flex gap-6">
                <a class="font-label-md text-label-md text-on-surface-variant hover:text-secondary transition-colors" href="#">Privacy</a>
                <a class="font-label-md text-label-md text-on-surface-variant hover:text-secondary transition-colors" href="#">Terms</a>
                <a class="font-label-md text-label-md text-on-surface-variant hover:text-secondary transition-colors" href="#">RSS</a>
                <a class="font-label-md text-label-md text-on-surface-variant hover:text-secondary transition-colors" href="#">Contact</a>
            </nav>
        </div>
    </footer>
</template>

<style>
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    vertical-align: middle;
}
.progress-bar {
    height: 2px;
    background: #47645d;
    position: fixed;
    top: 0;
    left: 0;
    width: 0%;
    z-index: 100;
    transition: width 0.1s ease-out;
}
::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-track { background: #faf9f7; }
::-webkit-scrollbar-thumb { background: #c4c7c7; }
::-webkit-scrollbar-thumb:hover { background: #747878; }
</style>
