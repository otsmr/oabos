

class Feed{

    constructor(){

        this.r = new ParseRequest();

    }


    removeItem(id){

        $.post("/api/feed.php", {type: "removeItem", id: id}).done((r)=>{
            this.r.parse(r, (r)=>{
                $(`[item-id=${id}]`).remove();
                $(`[from=${id}]`).remove();
                console.log(r);
            });
        });

    }

    addFeed(name, feed){
        $.post("/api/feed.php", {type: "addFeed", id: feed, name: name}).done((r)=>{
            this.r.parse(r, (r)=>{
                location.reload();
            });
        });
    }

}
var f = new Feed();

$("#addFeed").click(function(){
    new Dialog({
        title: "Feed hinzufügen",
        desc: "",
        ok: "Hinzufügen",
        cancel: "Abbrechen",
        input1 : "<input placeholder='Feedname'>",
        input2: "<input placeholder='RSS-Feed oder YouTube Name/Kanal ID'>"
    } , (name, feed)=>{
        console.log(name, feed);
        if(feed) f.addFeed(name, feed);
    });
});

$(".settings").click(function(e){

    function remove($menu, force){
        if(!force && $menu.is(":hover")) return;
        $menu.remove();
        $("html").off("click");
    }

    e.preventDefault();
    var $menu;
    var itemID = $(this).parent().parent().attr("item-id");

    $menu = $("<div class='menuItem'>").css({
        top: e.clientY,
        left: e.clientX,
    }).append(
        $("<ul>").append(
            $("<li>Entfernen</li>").click(()=>{
                f.removeItem(itemID);
                remove($menu, true);
            }),
            $("<li>Webseite öffnen</li>").click(()=>{
                remove($menu, true);
            }),
        )
    ).appendTo("body");

    $("html").off("mousedown").mousedown(()=>{
        remove($menu);
    });

});


$("[item-id]").click(function(){

    if($(".icon:hover").length > 0) return;

    var itemID = $(this).attr("item-id");

    if($(this).hasClass("aktiv")){
        itemID = "";
        $(".item").removeClass("diabled");
        $("[fortime]").fadeIn(0);
        $("[item-id]").removeClass("aktiv");
    }else{
        $("[item-id]").removeClass("aktiv");
        $(this).addClass("aktiv");
        $(".item").addClass("diabled");
        $(`[from=${itemID}]`).removeClass("diabled")
        $(`[time]`).fadeIn(0);

        $.each( $("[fortime]"), function( key, value ) {
            var aktive = $(value).children(".item").length - $(value).children(".item.diabled").length;
            $(value).fadeIn(0)
            if(aktive === 0){
                $(value).fadeOut(0);
                var time = $(value).attr("fortime");
                $(`[time=${time}]`).fadeOut(0);
            }
        });
    }

});

const displayImages = () => {

    const displayLine = $(".items")[0].scrollTop + $(".items").height();

    $(".item img").each((i, item)=>{

        if ((item.offsetTop + item.clientHeight) - displayLine < 0 && $(item).attr("link") !== "") {
            $(item).attr("src", $(item).attr("link"));
            $(item).attr("link", "");
        }
    });

}
$(()=>{
    displayImages();
})
$(".items").scroll(displayImages);

$("[yt-id]").click((event) =>{

    const $e = $(event.currentTarget);
    const videoID = $e.attr("yt-id");
    $(".playerAktiv").removeClass("playerAktiv")

    if($("#yt-player").length > 0){
        const closedID = $("#yt-player").attr("yt-id");
        $("#yt-player").remove();
        if(closedID === videoID) return;
    }

    const src = `https://www.youtube-nocookie.com/embed/${videoID}?color=white&modestbranding=1`;

    var $iframe = $(`<iframe yt-id='${videoID}' id='yt-player' src='${src}' type='text/html' frameborder=0 allowfullscreen>`)
    $e.addClass("playerAktiv").find("img").after($iframe);

});