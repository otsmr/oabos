// Global watch progress variables
let activeVideoID = null;
let activeElement = null;
let activeCurrentTime = 0;
let activeDuration = 0;
let activeMessageListener = null;

function getWatchProgress(videoID) {
    try {
        const progress = JSON.parse(localStorage.getItem('yt_watch_progress') || '{}');
        return progress[videoID] || null;
    } catch (e) {
        return null;
    }
}

function saveWatchProgress(videoID, time, duration) {
    try {
        const progress = JSON.parse(localStorage.getItem('yt_watch_progress') || '{}');
        const percentage = duration > 0 ? (time / duration) * 100 : 0;
        progress[videoID] = {
            time: time,
            duration: duration,
            percentage: percentage
        };
        localStorage.setItem('yt_watch_progress', JSON.stringify(progress));
    } catch (e) {
        console.error("Error saving watch progress", e);
    }
}

function updateCardProgress(card, percentage) {
    let container = card.querySelector(".watch-progress-container");
    if (!container) {
        const thumbnailWrapper = card.querySelector(".thumbnail-wrapper");
        if (!thumbnailWrapper) return;
        container = document.createElement("div");
        container.classList.add("watch-progress-container");
        const fill = document.createElement("div");
        fill.classList.add("watch-progress-fill");
        container.appendChild(fill);
        thumbnailWrapper.appendChild(container);
    }
    const fill = container.querySelector(".watch-progress-fill");
    if (fill) {
        const pct = Math.min(100, Math.max(0, percentage));
        fill.style.width = `${pct}%`;
    }
}

function renderWatchProgress() {
    let progress = {};
    try {
        progress = JSON.parse(localStorage.getItem('yt_watch_progress') || '{}');
    } catch (e) {
        return;
    }
    
    document.querySelectorAll("[yt-id]").forEach(card => {
        const videoID = card.getAttribute("yt-id");
        if (!videoID) return;
        
        const data = progress[videoID];
        card.querySelector(".watch-progress-container")?.remove();
        
        if (data && data.percentage > 0) {
            const thumbnailWrapper = card.querySelector(".thumbnail-wrapper");
            if (thumbnailWrapper) {
                const container = document.createElement("div");
                container.classList.add("watch-progress-container");
                const fill = document.createElement("div");
                fill.classList.add("watch-progress-fill");
                const pct = Math.min(100, Math.max(0, data.percentage));
                fill.style.width = `${pct}%`;
                container.appendChild(fill);
                thumbnailWrapper.appendChild(container);
            }
        }
    });
}

function syncProgressToServer(videoID, time, duration, percentage) {
    const formData = new FormData();
    formData.append('type', 'save_progress');
    formData.append('video_id', videoID);
    formData.append('time', time);
    formData.append('duration', duration);
    formData.append('percentage', percentage);
    
    fetch('/api/public.php', {
        method: 'POST',
        body: formData
    }).catch(e => console.error("Error syncing progress to server", e));
}

function cleanupActivePlayer() {
    if (activeVideoID && activeElement && activeDuration > 0) {
        try {
            saveWatchProgress(activeVideoID, activeCurrentTime, activeDuration);
            const pct = (activeCurrentTime / activeDuration) * 100;
            updateCardProgress(activeElement, pct);
            syncProgressToServer(activeVideoID, activeCurrentTime, activeDuration, pct);
        } catch (e) {
            console.error("Error saving progress on cleanup", e);
        }
    }
    if (activeMessageListener) {
        window.removeEventListener("message", activeMessageListener);
        activeMessageListener = null;
    }
    activeVideoID = null;
    activeElement = null;
    activeCurrentTime = 0;
    activeDuration = 0;
}

function trigger(selector, event, fnc) {
    document.querySelector(selector)?.addEventListener(event, fnc);
}
function triggerAll(selector, event, fnc) {
    [...document.querySelectorAll(selector)].forEach(element => {
        element.addEventListener(event, fnc);
    });
}
const $ = s => document.querySelector(s);

function removeFromFeed (id) {

    if (confirm("Sicher?")) {

        const formData = new FormData();
        formData.append('type', 'remove');
        formData.append('id', id);

        fetch("/api/public.php", {
            method: "POST",
            body: formData
        }).then(_ => {
            location.reload();
        })
        
    }

}

function addToFeed () {
    closeSidebar();
    const modal = document.getElementById("addChannelModal");
    if (modal) {
        modal.classList.add("modal-open");
        const urlInput = document.getElementById("channelUrlInput");
        if (urlInput) {
            urlInput.value = "";
            urlInput.focus();
        }
        const errorDiv = document.getElementById("addChannelError");
        if (errorDiv) errorDiv.style.display = "none";
        const form = document.getElementById("addChannelForm");
        if (form) form.style.display = "block";
        const loadingDiv = document.getElementById("addChannelLoading");
        if (loadingDiv) loadingDiv.style.display = "none";
    }
}

triggerAll("[yt-id]", "click", (event) =>{

    const element = event.currentTarget;
    const videoID = element.attributes["yt-id"].value;

    function remove () {
        cleanupActivePlayer();
        $(".playerAktiv")?.classList.remove("playerAktiv");
        $(".overlay")?.remove();
        $("#ytplayer")?.remove();
    }

    const closeID = $("#ytplayer")?.attributes["yt-id"].value
    remove();
    
    if(closeID === videoID)
        return;

    const overlay = document.createElement("div");
    overlay.classList.add("overlay");
    overlay.addEventListener("click", remove);

    document.body.appendChild(overlay);

    const saved = getWatchProgress(videoID);
    let startOffset = 0;
    if (saved && saved.time > 5) {
        const pct = saved.percentage || (saved.time / saved.duration) * 100;
        if (pct < 95) {
            startOffset = Math.floor(saved.time);
        }
    }

    const iframe = document.createElement("iframe");
    iframe.id = "ytplayer";
    iframe.setAttribute("yt-id", videoID);
    iframe.setAttribute("frameborder", "0");
    iframe.setAttribute("type", "text/html");
    iframe.setAttribute("allowfullscreen", "true");
    iframe.setAttribute("allow", "autoplay; encrypted-media");
    iframe.setAttribute("referrerpolicy", "strict-origin-when-cross-origin");

    let src = `https://www.youtube-nocookie.com/embed/${videoID}?color=white&modestbranding=1&enablejsapi=1`;
    if (startOffset > 0) {
        src += `&start=${startOffset}`;
    }
    iframe.src = src;

    element.classList.add("playerAktiv");
    
    const thumbnailWrapper = element.querySelector(".thumbnail-wrapper");
    if (thumbnailWrapper) {
        thumbnailWrapper.after(iframe);
    } else {
        element.querySelector("img").after(iframe);
    }

    activeVideoID = videoID;
    activeElement = element;

    activeCurrentTime = startOffset;
    activeDuration = 0;
    let lastSyncTime = Date.now();

    activeMessageListener = (event) => {
        if (!event.origin.match(/^https?:\/\/(www\.)?youtube(-nocookie)?\.com$/)) {
            return;
        }

        try {
            const data = JSON.parse(event.data);
            
            if (data.event === 'initialDelivery') {
                iframe.contentWindow.postMessage(JSON.stringify({ event: 'listening' }), '*');
            }
            
            if (data.event === 'infoDelivery' && data.info) {
                if (data.info.currentTime !== undefined) {
                    activeCurrentTime = data.info.currentTime;
                }
                if (data.info.duration !== undefined) {
                    activeDuration = data.info.duration;
                }
                
                if (activeDuration > 0) {
                    saveWatchProgress(videoID, activeCurrentTime, activeDuration);
                    const pct = (activeCurrentTime / activeDuration) * 100;
                    updateCardProgress(element, pct);
                    
                    const now = Date.now();
                    if (now - lastSyncTime >= 5000) {
                        lastSyncTime = now;
                        syncProgressToServer(videoID, activeCurrentTime, activeDuration, pct);
                    }
                }
            }
            
            if (data.event === 'onStateChange') {
                const state = data.info;
                if (state === 2 || state === 0) {
                    if (activeDuration > 0) {
                        const pct = (activeCurrentTime / activeDuration) * 100;
                        syncProgressToServer(videoID, activeCurrentTime, activeDuration, pct);
                    }
                }
            }
        } catch (e) {
            // Ignore parse errors from non-JSON messages
        }
    };

    window.addEventListener("message", activeMessageListener);

    iframe.addEventListener("load", () => {
        let attempts = 0;
        const interval = setInterval(() => {
            if (attempts > 10 || !document.getElementById("ytplayer")) {
                clearInterval(interval);
                return;
            }
            try {
                iframe.contentWindow.postMessage(JSON.stringify({ event: 'listening' }), '*');
            } catch (e) {}
            attempts++;
        }, 250);
    });
});

function displayImages () {
    const scrollContainer = $(".feed-wrapper");
    if (!scrollContainer) return;

    if (!('IntersectionObserver' in window)) {
        // Fallback for older browsers: load all images immediately
        document.querySelectorAll(".item img").forEach(item => {
            const link = item.getAttribute("link");
            if (link) {
                item.src = link;
                item.removeAttribute("link");
            }
        });
        return;
    }

    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                const link = img.getAttribute("link");
                if (link) {
                    img.src = link;
                    img.removeAttribute("link");
                }
                obs.unobserve(img);
            }
        });
    }, {
        root: scrollContainer,
        rootMargin: "200px 0px", // Preload images 200px before they scroll into view
        threshold: 0.01
    });

    document.querySelectorAll(".item img").forEach(img => {
        if (img.getAttribute("link")) {
            observer.observe(img);
        }
    });
}

function humanTimeDiff(d) {

    diff = new Date().getTime() - d.getTime();
    diff = parseInt(diff / 1000 / 60 / 60 / 24);
    display = "";

    switch(diff) {
        case 0: display = "Heute";break;
        case 1: display = "Gestern"; break;
        default:
            display = "vor ";
            if (diff > 365) {
                display += parseInt(diff / 365) + " Jahr(en)";
            } else {
                display += diff + " Tagen";
            }
    }
    return display;

}

[...document.querySelectorAll("span.time")].forEach(element => {
    
    element.innerText = humanTimeDiff(new Date(element.innerText.slice(0, 10)));

})

window.onload = () => {
    if (window.serverWatchProgress) {
        try {
            localStorage.setItem('yt_watch_progress', JSON.stringify(window.serverWatchProgress));
        } catch (e) {
            console.error("Error setting server watch progress in localStorage", e);
        }
    }
    displayImages();
    renderWatchProgress();
};

// Login / Register Form Toggling
const authContainer = document.getElementById('authContainer');
if (authContainer) {
    document.getElementById('switchToRegister')?.addEventListener('click', (e) => {
        e.preventDefault();
        authContainer.setAttribute('data-mode', 'register');
    });
    document.getElementById('switchToLogin')?.addEventListener('click', (e) => {
        e.preventDefault();
        authContainer.setAttribute('data-mode', 'login');
    });

    // Show loading overlay on form submission
    const loginForm = document.querySelector('.login-form');
    const registerForm = document.querySelector('.register-form');
    const authLoadingText = document.getElementById('authLoadingText');

    loginForm?.addEventListener('submit', () => {
        authContainer.classList.add('auth-loading');
        if (authLoadingText) {
            authLoadingText.textContent = 'Anmeldung läuft...';
        }
    });

    registerForm?.addEventListener('submit', () => {
        authContainer.classList.add('auth-loading');
        if (authLoadingText) {
            authLoadingText.textContent = 'Konto wird erstellt...';
        }
    });
}

// Add Channel Modal event listeners
const addChannelModal = document.getElementById("addChannelModal");
const channelUrlInput = document.getElementById("channelUrlInput");
const addChannelForm = document.getElementById("addChannelForm");
const addChannelLoading = document.getElementById("addChannelLoading");
const addChannelError = document.getElementById("addChannelError");

function closeChannelModal() {
    addChannelModal?.classList.remove("modal-open");
}

document.getElementById("closeAddChannelModal")?.addEventListener("click", closeChannelModal);
document.getElementById("cancelAddChannel")?.addEventListener("click", closeChannelModal);

// Close modal when clicking outside the card
addChannelModal?.addEventListener("click", (e) => {
    if (e.target === addChannelModal) {
        closeChannelModal();
    }
});

addChannelForm?.addEventListener("submit", (e) => {
    e.preventDefault();
    const url = channelUrlInput.value.trim();
    if (!url) return;

    addChannelForm.style.display = "none";
    addChannelLoading.style.display = "flex";
    addChannelError.style.display = "none";

    const formData = new FormData();
    formData.append('type', 'add');
    formData.append("url", url);

    fetch("/api/public.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data && data.error) {
            addChannelError.innerText = data.error;
            addChannelError.style.display = "block";
            addChannelForm.style.display = "block";
            addChannelLoading.style.display = "none";
        } else {
            location.reload();
        }
    })
    .catch(err => {
        console.error(err);
        addChannelError.innerText = "Ein Fehler ist aufgetreten.";
        addChannelError.style.display = "block";
        addChannelForm.style.display = "block";
        addChannelLoading.style.display = "none";
    });
});

// Mobile Sidebar Toggle and Overlay interactions
function openSidebar() {
    document.querySelector(".sidebar")?.classList.add("sidebar-open");
    document.getElementById("sidebarOverlay")?.classList.add("active");
}

function closeSidebar() {
    document.querySelector(".sidebar")?.classList.remove("sidebar-open");
    document.getElementById("sidebarOverlay")?.classList.remove("active");
}

document.getElementById("mobileSidebarToggle")?.addEventListener("click", openSidebar);
document.getElementById("sidebarOverlay")?.addEventListener("click", closeSidebar);

// Close sidebar on logout/signout
document.querySelector(".logoutbtn-link")?.addEventListener("click", closeSidebar);