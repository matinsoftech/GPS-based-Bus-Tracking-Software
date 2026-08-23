<header
    x-data="{menuToggle: false}"
    class="sticky top-0 z-99999 flex w-full border-gray-200 bg-white lg:border-b dark:border-gray-800 dark:bg-gray-900"
>
    <div
        class="flex grow flex-col items-center justify-between lg:flex-row lg:px-6"
    >
        <div
            class="flex w-full items-center justify-between gap-2 border-b border-gray-200 px-3 py-3 sm:gap-4 lg:justify-normal lg:border-b-0 lg:px-0 lg:py-4 dark:border-gray-800"
        >
            <!-- Hamburger Toggle BTN -->
            <button
                :class="sidebarToggle ? 'lg:bg-transparent dark:lg:bg-transparent bg-gray-100 dark:bg-gray-800' : ''"
                class="z-99999 flex h-10 w-10 items-center justify-center rounded-lg border-gray-200 text-gray-500 lg:h-11 lg:w-11 lg:border dark:border-gray-800 dark:text-gray-400"
                @click.stop="sidebarToggle = !sidebarToggle"
            >
                <svg
                    class="hidden fill-current lg:block"
                    width="16"
                    height="12"
                    viewBox="0 0 16 12"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M0.583252 1C0.583252 0.585788 0.919038 0.25 1.33325 0.25H14.6666C15.0808 0.25 15.4166 0.585786 15.4166 1C15.4166 1.41421 15.0808 1.75 14.6666 1.75L1.33325 1.75C0.919038 1.75 0.583252 1.41422 0.583252 1ZM0.583252 11C0.583252 10.5858 0.919038 10.25 1.33325 10.25L14.6666 10.25C15.0808 10.25 15.4166 10.5858 15.4166 11C15.4166 11.4142 15.0808 11.75 14.6666 11.75L1.33325 11.75C0.919038 11.75 0.583252 11.4142 0.583252 11ZM1.33325 5.25C0.919038 5.25 0.583252 5.58579 0.583252 6C0.583252 6.41421 0.919038 6.75 1.33325 6.75L7.99992 6.75C8.41413 6.75 8.74992 6.41421 8.74992 6C8.74992 5.58579 8.41413 5.25 7.99992 5.25L1.33325 5.25Z"
                        fill=""
                    />
                </svg>

                <svg
                    :class="sidebarToggle ? 'hidden' : 'block lg:hidden'"
                    class="fill-current lg:hidden"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M3.25 6C3.25 5.58579 3.58579 5.25 4 5.25L20 5.25C20.4142 5.25 20.75 5.58579 20.75 6C20.75 6.41421 20.4142 6.75 20 6.75L4 6.75C3.58579 6.75 3.25 6.41422 3.25 6ZM3.25 18C3.25 17.5858 3.58579 17.25 4 17.25L20 17.25C20.4142 17.25 20.75 17.5858 20.75 18C20.75 18.4142 20.4142 18.75 20 18.75L4 18.75C3.58579 18.75 3.25 18.4142 3.25 18ZM4 11.25C3.58579 11.25 3.25 11.5858 3.25 12C3.25 12.4142 3.58579 12.75 4 12.75L12 12.75C12.4142 12.75 12.75 12.4142 12.75 12C12.75 11.5858 12.4142 11.25 12 11.25L4 11.25Z"
                        fill=""
                    />
                </svg>

                <!-- cross icon -->
                <svg
                    :class="sidebarToggle ? 'block lg:hidden' : 'hidden'"
                    class="fill-current"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M6.21967 7.28131C5.92678 6.98841 5.92678 6.51354 6.21967 6.22065C6.51256 5.92775 6.98744 5.92775 7.28033 6.22065L11.999 10.9393L16.7176 6.22078C17.0105 5.92789 17.4854 5.92788 17.7782 6.22078C18.0711 6.51367 18.0711 6.98855 17.7782 7.28144L13.0597 12L17.7782 16.7186C18.0711 17.0115 18.0711 17.4863 17.7782 17.7792C17.4854 18.0721 17.0105 18.0721 16.7176 17.7792L11.999 13.0607L7.28033 17.7794C6.98744 18.0722 6.51256 18.0722 6.21967 17.7794C5.92678 17.4865 5.92678 17.0116 6.21967 16.7187L10.9384 12L6.21967 7.28131Z"
                        fill=""
                    />
                </svg>
            </button>
            <!-- Hamburger Toggle BTN -->

            <a href="{{ auth()->user()->can('dashboard.view') ? route('dashboard') : route('profile.edit') }}" class="lg:hidden">
                <img class="dark:hidden" src="/images/logo/logo.svg" alt="Logo" />
                <img
                    class="hidden dark:block"
                    src="/images/logo/logo-dark.svg"
                    alt="Logo"
                />
            </a>

            <!-- Application nav menu button -->
            <button
                class="z-99999 flex h-10 w-10 items-center justify-center rounded-lg text-gray-700 hover:bg-gray-100 lg:hidden dark:text-gray-400 dark:hover:bg-gray-800"
                :class="menuToggle ? 'bg-gray-100 dark:bg-gray-800' : ''"
                @click.stop="menuToggle = !menuToggle"
            >
                <svg
                    class="fill-current"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M5.99902 10.4951C6.82745 10.4951 7.49902 11.1667 7.49902 11.9951V12.0051C7.49902 12.8335 6.82745 13.5051 5.99902 13.5051C5.1706 13.5051 4.49902 12.8335 4.49902 12.0051V11.9951C4.49902 11.1667 5.1706 10.4951 5.99902 10.4951ZM17.999 10.4951C18.8275 10.4951 19.499 11.1667 19.499 11.9951V12.0051C19.499 12.8335 18.8275 13.5051 17.999 13.5051C17.1706 13.5051 16.499 12.8335 16.499 12.0051V11.9951C16.499 11.1667 17.1706 10.4951 17.999 10.4951ZM13.499 11.9951C13.499 11.1667 12.8275 10.4951 11.999 10.4951C11.1706 10.4951 10.499 11.1667 10.499 11.9951V12.0051C10.499 12.8335 11.1706 13.5051 11.999 13.5051C12.8275 13.5051 13.499 12.8335 13.499 12.0051V11.9951Z"
                        fill=""
                    />
                </svg>
            </button>
            <!-- Application nav menu button -->

            <div class="hidden lg:block">
                <form>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2">
                            <svg
                                class="fill-gray-500 dark:fill-gray-400"
                                width="20"
                                height="20"
                                viewBox="0 0 20 20"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z"
                                    fill=""
                                />
                            </svg>
                        </span>
                        <input
                            type="text"
                            placeholder="Search or type command..."
                            id="search-input"
                            class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-200 bg-transparent py-2.5 pr-14 pl-12 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden xl:w-[430px] dark:border-gray-800 dark:bg-gray-900 dark:bg-white/[0.03] dark:text-white/90 dark:placeholder:text-white/30"
                        />

                        <button
                            id="search-button"
                            class="absolute right-2.5 top-1/2 inline-flex -translate-y-1/2 items-center gap-0.5 rounded-lg border border-gray-200 bg-gray-50 px-[7px] py-[4.5px] text-xs -tracking-[0.2px] text-gray-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400"
                        >
                            <span> ⌘ </span>
                            <span> K </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div
            :class="menuToggle ? 'flex' : 'hidden'"
            class="shadow-theme-md w-full items-center justify-between gap-4 px-5 py-4 lg:flex lg:justify-end lg:px-0 lg:shadow-none"
        >
            <div class="2xsm:gap-3 flex items-center gap-2">
                <!-- Dark Mode Toggler -->
                <button
                    class="hover:text-dark-900 relative flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
                    @click.prevent="darkMode = !darkMode"
                >
                    <svg
                        class="hidden dark:block"
                        width="20"
                        height="20"
                        viewBox="0 0 20 20"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                            d="M9.99998 1.5415C10.4142 1.5415 10.75 1.87729 10.75 2.2915V3.5415C10.75 3.95572 10.4142 4.2915 9.99998 4.2915C9.58577 4.2915 9.24998 3.95572 9.24998 3.5415V2.2915C9.24998 1.87729 9.58577 1.5415 9.99998 1.5415ZM10.0009 6.79327C8.22978 6.79327 6.79402 8.22904 6.79402 10.0001C6.79402 11.7712 8.22978 13.207 10.0009 13.207C11.772 13.207 13.2078 11.7712 13.2078 10.0001C13.2078 8.22904 11.772 6.79327 10.0009 6.79327ZM5.29402 10.0001C5.29402 7.40061 7.40135 5.29327 10.0009 5.29327C12.6004 5.29327 14.7078 7.40061 14.7078 10.0001C14.7078 12.5997 12.6004 14.707 10.0009 14.707C7.40135 14.707 5.29402 12.5997 5.29402 10.0001ZM15.9813 5.08035C16.2742 4.78746 16.2742 4.31258 15.9813 4.01969C15.6884 3.7268 15.2135 3.7268 14.9207 4.01969L14.0368 4.90357C13.7439 5.19647 13.7439 5.67134 14.0368 5.96423C14.3297 6.25713 14.8045 6.25713 15.0974 5.96423L15.9813 5.08035ZM18.4577 10.0001C18.4577 10.4143 18.1219 10.7501 17.7077 10.7501H16.4577C16.0435 10.7501 15.7077 10.4143 15.7077 10.0001C15.7077 9.58592 16.0435 9.25013 16.4577 9.25013H17.7077C18.1219 9.25013 18.4577 9.58592 18.4577 10.0001ZM14.9207 15.9806C15.2135 16.2735 15.6884 16.2735 15.9813 15.9806C16.2742 15.6877 16.2742 15.2128 15.9813 14.9199L15.0974 14.036C14.8045 13.7431 14.3297 13.7431 14.0368 14.036C13.7439 14.3289 13.7439 14.8038 14.0368 15.0967L14.9207 15.9806ZM9.99998 15.7088C10.4142 15.7088 10.75 16.0445 10.75 16.4588V17.7088C10.75 18.123 10.4142 18.4588 9.99998 18.4588C9.58577 18.4588 9.24998 18.123 9.24998 17.7088V16.4588C9.24998 16.0445 9.58577 15.7088 9.99998 15.7088ZM5.96356 15.0972C6.25646 14.8043 6.25646 14.3295 5.96356 14.0366C5.67067 13.7437 5.1958 13.7437 4.9029 14.0366L4.01902 14.9204C3.72613 15.2133 3.72613 15.6882 4.01902 15.9811C4.31191 16.274 4.78679 16.274 5.07968 15.9811L5.96356 15.0972ZM4.29224 10.0001C4.29224 10.4143 3.95645 10.7501 3.54224 10.7501H2.29224C1.87802 10.7501 1.54224 10.4143 1.54224 10.0001C1.54224 9.58592 1.87802 9.25013 2.29224 9.25013H3.54224C3.95645 9.25013 4.29224 9.58592 4.29224 10.0001ZM3.92068 3.92068C4.21357 3.62779 4.68845 3.62779 4.98134 3.92068L5.86522 4.80456C6.15811 5.09745 6.15811 5.57233 5.86522 5.86522C5.57233 6.15811 5.09745 6.15811 4.80456 5.86522L3.92068 4.98134C3.62779 4.68845 3.62779 4.21357 3.92068 3.92068ZM3.92068 16.0793C3.62779 15.7864 3.62779 15.3116 3.92068 15.0187L4.80456 14.1348C5.09745 13.8419 5.57233 13.8419 5.86522 14.1348C6.15811 14.4277 6.15811 14.9026 5.86522 15.1955L4.98134 16.0793C4.68845 16.3722 4.21357 16.3722 3.92068 16.0793ZM9.99998 1.5415Z"
                            fill="currentColor"
                        />
                    </svg>
                    <svg
                        class="dark:hidden"
                        width="20"
                        height="20"
                        viewBox="0 0 20 20"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M17.4547 11.97L18.1799 12.1611C18.265 11.8383 18.1265 11.4982 17.8401 11.3266C17.5538 11.1551 17.1885 11.1934 16.944 11.4207L17.4547 11.97ZM8.0306 2.5459L8.57989 3.05657C8.80718 2.81209 8.84554 2.44682 8.67398 2.16046C8.50243 1.8741 8.16227 1.73559 7.83948 1.82066L8.0306 2.5459ZM12.9154 13.0035C9.64678 13.0035 6.99707 10.3538 6.99707 7.08524H5.49707C5.49707 11.1823 8.81835 14.5035 12.9154 14.5035V13.0035ZM16.944 11.4207C15.8869 12.4035 14.4721 13.0035 12.9154 13.0035V14.5035C14.8657 14.5035 16.6418 13.7499 17.9654 12.5193L16.944 11.4207ZM16.7295 11.7789C15.9437 14.7607 13.2277 16.9586 10.0003 16.9586V18.4586C13.9257 18.4586 17.2249 15.7853 18.1799 12.1611L16.7295 11.7789ZM10.0003 16.9586C6.15734 16.9586 3.04199 13.8433 3.04199 10.0003H1.54199C1.54199 14.6717 5.32892 18.4586 10.0003 18.4586V16.9586ZM3.04199 10.0003C3.04199 6.77289 5.23988 4.05695 8.22173 3.27114L7.83948 1.82066C4.21532 2.77574 1.54199 6.07486 1.54199 10.0003H3.04199ZM6.99707 7.08524C6.99707 5.52854 7.5971 4.11366 8.57989 3.05657L7.48132 2.03522C6.25073 3.35885 5.49707 5.13487 5.49707 7.08524H6.99707Z"
                            fill="currentColor"
                        />
                    </svg>
                </button>
                <!-- Dark Mode Toggler -->

                <!-- Notification Menu Area -->
                <div
                    class="relative"
                    x-data="{ dropdownOpen: false, unreadCount: {{ $headerUnreadCount }} }"
                    x-init="setInterval(() => {
                        fetch('{{ route('notifications.unread-count') }}', { headers: { 'Accept': 'application/json' } })
                            .then(r => r.json())
                            .then(d => { if (d.count != null) unreadCount = d.count; })
                            .catch(() => {});
                    }, 30000)"
                    @click.outside="dropdownOpen = false"
                >
                    <button
                        class="hover:text-dark-900 relative flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
                        @click.prevent="dropdownOpen = ! dropdownOpen"
                    >
                        <svg
                            class="fill-current"
                            width="22"
                            height="22"
                            viewBox="0 0 22 22"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                fill-rule="evenodd"
                                clip-rule="evenodd"
                                d="M11 2C6.85786 2 3.5 5.35786 3.5 9.5V12.1024L1.81585 16.2987C1.56313 16.9647 2.05095 17.6848 2.75902 17.7273L8.50949 18.0712C8.53604 18.0729 8.56245 18.075 8.5887 18.0775C8.52942 18.2476 8.5 18.4251 8.5 18.6051C8.5 20.0248 9.46864 21.1992 10.7774 21.5324C10.884 21.5602 10.9928 21.5744 11.1013 21.5744C11.21 21.5744 11.3187 21.5602 11.4254 21.5324C11.7259 21.4551 12.0042 21.3169 12.2443 21.1306C12.1014 21.2447 11.9687 21.3722 11.8487 21.5112L12.4347 21.9266L12.4347 21.9266L11.8487 21.5112C12.0283 21.3022 12.1556 21.0468 12.2187 20.7659C12.3327 20.2782 12.1869 19.8032 11.8867 19.4507C11.6954 19.2283 11.4335 19.0652 11.1391 18.9879C10.9177 18.9282 10.6889 18.9259 10.4686 18.9702L10.4837 19.0165C10.448 19.0246 10.4119 19.0297 10.3753 19.0317C10.3289 19.0055 10.2823 18.9795 10.2355 18.9539L3.32497 18.5433C3.09638 18.5301 2.9404 18.319 2.98067 18.0939L3.5 15.2789V9.5C3.5 5.633 6.633 2.5 10.5 2.5C14.367 2.5 17.5 5.633 17.5 9.5V12.9734L18.6576 15.8825C18.7532 16.1063 18.9967 16.2355 19.2415 16.1825L19.7339 16.0724L19.7339 16.0724L18.1397 17.7459L18.6622 16.1639C18.6535 16.1891 18.6357 16.2107 18.612 16.2244L18.2102 16.4529L18.0394 16.5471L18.019 16.4371L17.7647 15.7722L17.7434 15.7132L17.6594 15.4949L17.5779 15.2753L17.529 15.1491L17.3885 14.7812L17.3733 14.7412L17.3337 14.6306L17.3276 14.6134L17.2983 14.5303L17.2775 14.4734C16.874 13.8818 16.6666 13.1946 16.6666 12.5V9.5C16.6666 5.35786 13.1421 2 11 2ZM16.6666 12.5C16.6666 13.1946 16.874 13.8818 17.2775 14.4734L17.2983 14.5303L17.3276 14.6134L17.3337 14.6306L17.3733 14.7412L17.3885 14.7812L17.529 15.1491L17.5779 15.2753L17.6594 15.4949L17.7434 15.7132L17.7647 15.7722L18.019 16.4371L18.0394 16.5471L18.2102 16.4529L18.612 16.2244C18.7578 16.1423 18.9406 16.1712 19.052 16.2938L19.7942 17.1075C19.9554 17.2832 20.0704 17.4973 20.1297 17.7302C20.189 17.963 20.1907 18.2075 20.1347 18.4412C20.0787 18.6749 19.9671 18.8906 19.8088 19.0685C19.6505 19.2464 19.4508 19.381 19.2277 19.4601L18.3353 19.7806C17.9608 19.9097 17.5238 19.8238 17.2324 19.5511C16.941 19.2785 16.8264 18.8647 16.9255 18.4519L16.6666 12.5ZM6.16587 17.6527L5.69399 19.5H6.47123L7.05812 18.1817L6.16587 17.6527ZM10.2795 19.6851L11.0325 20.8894C10.7535 21.1838 10.4052 21.4068 10.0084 21.5073C9.38839 21.6722 8.73164 21.4669 8.3054 20.9691C7.87915 20.4713 7.76076 19.7843 8.00037 19.1754L8.26247 18.4412C8.67092 18.4129 9.04748 18.2706 9.36444 18.0361C9.74125 17.7552 10.0217 17.3789 10.1603 16.9505C10.1961 16.83 10.2209 16.7062 10.2343 16.5808C10.3918 16.626 10.5333 16.708 10.6504 16.8181C10.8474 16.9937 10.9831 17.2257 11.0404 17.4822C11.0788 17.6545 11.0934 17.8318 11.0837 18.0084C11.073 18.2966 11.0107 18.5805 10.8988 18.8463C10.7909 19.1033 10.5927 19.4472 10.2795 19.6851ZM8.26247 18.4412L7.625 20.25H4.90922C4.87945 20.2499 4.85058 20.241 4.82528 20.2242L3.11849 19.4303C2.8305 19.3014 2.71055 18.9638 2.82536 18.6691C2.94016 18.3744 3.25079 18.1856 3.56732 18.2199L8.26247 18.4412Z"
                                fill=""
                            />
                        </svg>

                        <span
                            x-show="unreadCount > 0"
                            x-cloak
                            class="absolute right-0.5 top-0.5 z-10 flex h-4 min-w-4 items-center justify-center rounded-full bg-error-500 px-1 text-[10px] font-semibold text-white">
                            <span x-text="unreadCount > 99 ? '99+' : unreadCount">0</span>
                        </span>
                    </button>

                    <!-- Dropdown Start -->
                    <div
                        x-show="dropdownOpen"
                        class="shadow-theme-lg dark:bg-gray-dark absolute -right-7 mt-[17px] flex h-[480px] w-[350px] flex-col rounded-2xl border border-gray-200 bg-white p-4 sm:-right-8 sm:w-[361px] dark:border-gray-800"
                    >
                        <div class="flex items-center justify-between pb-3">
                            <p class="text-lg font-semibold text-gray-800 dark:text-white/90">
                                Notifications
                            </p>
                            @if ($headerUnreadCount > 0)
                                <form method="POST" action="{{ route('notifications.read-all') }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="text-theme-xs cursor-pointer rounded-md px-1 py-0.5 text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/10"
                                    >
                                        Mark all as read
                                    </button>
                                </form>
                            @endif
                        </div>

                        <ul class="flex h-auto flex-col overflow-hidden overflow-y-auto custom-scrollbar">
                            @forelse ($headerNotifications as $notification)
                                @php
                                    $unread = is_null($notification->read_at);
                                @endphp
                                <li>
                                    <a
                                        href="{{ route('notifications.index') }}"
                                        class="flex gap-3 rounded-lg border-b border-gray-100 p-3 px-4.5 py-3 hover:bg-gray-100 dark:border-gray-800 dark:hover:bg-white/5 {{ $unread ? 'bg-brand-50/60 dark:bg-brand-500/10' : '' }}"
                                    >
                                        <x-notification-icon :notification="$notification" size="sm" />

                                        <span class="block min-w-0 flex-1">
                                            <span
                                                class="text-theme-sm mb-1 block truncate font-medium {{ $unread ? 'text-gray-800 dark:text-white/90' : 'text-gray-500 dark:text-gray-400' }}"
                                            >
                                                {{ $notification->data['title'] ?? 'Notification' }}
                                            </span>

                                            <span class="text-theme-xs block truncate text-gray-500 dark:text-gray-400">
                                                {{ $notification->data['message'] ?? '' }}
                                            </span>

                                            <span class="text-theme-xs mt-1 flex items-center gap-2 text-gray-500 dark:text-gray-400">
                                                <span>{{ $notification->created_at->diffForHumans() }}</span>
                                                @if ($unread)
                                                    <span class="h-1 w-1 rounded-full bg-brand-500"></span>
                                                    <span class="font-semibold text-brand-600 dark:text-brand-400">New</span>
                                                @endif
                                            </span>
                                        </span>
                                    </a>
                                </li>
                            @empty
                                <li class="flex flex-col items-center justify-center gap-2 py-12 text-center">
                                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                                            <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                                        </svg>
                                    </span>
                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">No notifications yet</p>
                                </li>
                            @endforelse
                        </ul>

                        <a
                            href="{{ route('notifications.index') }}"
                            class="text-theme-sm shadow-theme-xs mt-3 flex justify-center rounded-lg border border-gray-300 bg-white p-3 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
                        >
                            View All Notifications
                        </a>
                    </div>
                    <!-- Dropdown End -->
                </div>
                <!-- Notification Menu Area -->
            </div>

            <!-- User Area -->
            <div
                class="relative"
                x-data="{ dropdownOpen: false }"
                @click.outside="dropdownOpen = false"
            >
                <a
                    class="flex items-center text-gray-700 dark:text-gray-400"
                    href="#"
                    @click.prevent="dropdownOpen = ! dropdownOpen"
                >
                    <span class="mr-3 h-11 w-11 overflow-hidden rounded-full">
                        @if (Auth::user()->profile_photo)
                            <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="{{ Auth::user()->name }}" class="h-full w-full object-cover" />
                        @else
                            <img src="/images/user/owner.jpg" alt="User" class="h-full w-full object-cover" />
                        @endif
                    </span>

                    <span class="text-theme-sm mr-1 block font-medium">
                        {{ Auth::user()->name }}
                    </span>

                    <svg
                        :class="dropdownOpen && 'rotate-180'"
                        class="stroke-gray-500 dark:stroke-gray-400"
                        width="18"
                        height="20"
                        viewBox="0 0 18 20"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M4.3125 8.65625L9 13.3437L13.6875 8.65625"
                            stroke=""
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </a>

                <!-- Dropdown Start -->
                <div
                    x-show="dropdownOpen"
                    class="shadow-theme-lg dark:bg-gray-dark absolute right-0 mt-[17px] flex w-[260px] flex-col rounded-2xl border border-gray-200 bg-white p-3 dark:border-gray-800"
                >
                    <div>
                        <span
                            class="text-theme-sm block font-medium text-gray-700 dark:text-gray-400"
                        >
                            {{ Auth::user()->name }}
                        </span>
                        <span
                            class="text-theme-xs mt-0.5 block text-gray-500 dark:text-gray-400"
                        >
                            {{ Auth::user()->email }}
                        </span>
                    </div>

                    <ul
                        class="flex flex-col gap-1 border-b border-gray-200 pt-4 pb-3 dark:border-gray-800"
                    >
                        <li>
                            <a
                                href="{{ route('profile.edit') }}"
                                class="group text-theme-sm flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                            >
                                <svg
                                    class="fill-gray-500 group-hover:fill-gray-700 dark:fill-gray-400 dark:group-hover:fill-gray-300"
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        clip-rule="evenodd"
                                        d="M12 3.5C7.30558 3.5 3.5 7.30558 3.5 12C3.5 14.1526 4.3002 16.1184 5.61936 17.616C6.17279 15.3096 8.24852 13.5955 10.7246 13.5955H13.2746C15.7509 13.5955 17.8268 15.31 18.38 17.6167C19.6996 16.119 20.5 14.153 20.5 12C20.5 7.30558 16.6944 3.5 12 3.5ZM17.0246 18.8566V18.8455C17.0246 16.7744 15.3457 15.0955 13.2746 15.0955H10.7246C8.65354 15.0955 6.97461 16.7744 6.97461 18.8455V18.856C8.38223 19.8895 10.1198 20.5 12 20.5C13.8798 20.5 15.6171 19.8898 17.0246 18.8566ZM2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12ZM11.9991 7.25C10.8847 7.25 9.98126 8.15342 9.98126 9.26784C9.98126 10.3823 10.8847 11.2857 11.9991 11.2857C13.1135 11.2857 14.0169 10.3823 14.0169 9.26784C14.0169 8.15342 13.1135 7.25 11.9991 7.25ZM8.48126 9.26784C8.48126 7.32499 10.0563 5.75 11.9991 5.75C13.9419 5.75 15.5169 7.32499 15.5169 9.26784C15.5169 11.2107 13.9419 12.7857 11.9991 12.7857C10.0563 12.7857 8.48126 11.2107 8.48126 9.26784Z"
                                        fill=""
                                    />
                                </svg>
                                Edit profile
                            </a>
                        </li>
                    </ul>

                    <form method="POST" action="{{ route('logout') }}" class="mt-3">
                        @csrf
                        <button
                            type="submit"
                            class="group text-theme-sm flex w-full items-center gap-3 rounded-lg px-3 py-2 font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                        >
                            <svg
                                class="fill-gray-500 group-hover:fill-gray-700 dark:group-hover:fill-gray-300"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M15.1007 19.247C14.6865 19.247 14.3507 18.9112 14.3507 18.497L14.3507 14.245H12.8507V18.497C12.8507 19.7396 13.8581 20.747 15.1007 20.747H18.5007C19.7434 20.747 20.7507 19.7396 20.7507 18.497L20.7507 5.49609C20.7507 4.25345 19.7433 3.24609 18.5007 3.24609H15.1007C13.8581 3.24609 12.8507 4.25345 12.8507 5.49609V9.74501L14.3507 9.74501V5.49609C14.3507 5.08188 14.6865 4.74609 15.1007 4.74609L18.5007 4.74609C18.9149 4.74609 19.2507 5.08188 19.2507 5.49609L19.2507 18.497C19.2507 18.9112 18.9149 19.247 18.5007 19.247H15.1007ZM3.25073 11.9984C3.25073 12.2144 3.34204 12.4091 3.48817 12.546L8.09483 17.1556C8.38763 17.4485 8.86251 17.4487 9.15549 17.1559C9.44848 16.8631 9.44863 16.3882 9.15583 16.0952L5.81116 12.7484L16.0007 12.7484C16.4149 12.7484 16.7507 12.4127 16.7507 11.9984C16.7507 11.5842 16.4149 11.2484 16.0007 11.2484L5.81528 11.2484L9.15585 7.90554C9.44864 7.61255 9.44847 7.13767 9.15547 6.84488C9.86248 6.55209 8.3876 6.55226 8.09481 6.84525L3.52309 11.4202C3.35673 11.5577 3.25073 11.7657 3.25073 11.9984Z"
                                    fill=""
                                />
                            </svg>

                            Sign out
                        </button>
                    </form>
                </div>
                <!-- Dropdown End -->
            </div>
            <!-- User Area -->
        </div>
    </div>
</header>
