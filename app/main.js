
function trigger(selector, event, fnc) {
    document.querySelector(selector).addEventListener(event, fnc);
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
    const formData = new FormData();
    formData.append('type', 'add');
    formData.append("name", prompt("Name"))
    formData.append("id", prompt("ChannelID"))

    fetch("/api/public.php", {
        method: "POST",
        body: formData
    }).then(_ => {
        location.reload();
    })
    
}

triggerAll("[yt-id]", "click", (event) =>{

    const element = event.currentTarget;
    const videoID = element.attributes["yt-id"].value;

    $(".playerAktiv")?.classList.remove("playerAktiv")

    const player = $("#ytplayer");

    if (player !== null) {

        const closedID = $("#ytplayer").attributes["yt-id"].value;
        
        player.remove();

        if(closedID === videoID)
            return;

    }

    const iframe = document.createElement("iframe");
    iframe.id = "ytplayer";
    iframe.src = `https://www.youtube-nocookie.com/embed/${videoID}?color=white&modestbranding=1`;

    iframe.setAttribute("yt-id", videoID);
    iframe.setAttribute("frameborder", 0);
    iframe.setAttribute("type", "text/html");
    iframe.setAttribute("allowfullscreen", true);

    element.classList.add("playerAktiv");
    element.querySelector("img").after(iframe);

});

function displayImages () {

    const displayLine = $(".items").scrollTop + $(".items").offsetHeight;

    [...document.querySelectorAll(".item img")].forEach(item => {

        if ((item.offsetTop + item.clientHeight) - displayLine < 0 && item.attributes.link.value !== "") {
            item.src = item.attributes.link.value;
            item.attributes.link.value = "";
        }

    })

}

window.onload = displayImages;
trigger(".items", "scroll", displayImages)