<button
    x-data="{
        show: false,
        init() {
            window.addEventListener('scroll', () => {
                this.show = window.scrollY > 500;
            });
        }
    }"
    x-show="show"
    @click="window.scrollTo({top: 0, behavior: 'smooth'})"
    class="fixed bottom-20 right-6 p-3 rounded-full bg-white text-black shadow-md hover:bg-gray-100 z-50 border border-gray-200"
    style="right: 1.5rem; left: auto;"
    aria-label="Scroll to top"
>
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
    </svg>
</button>