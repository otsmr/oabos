
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

    function remove () {

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

window.onload = displayImages;
trigger(".items", "scroll", displayImages)