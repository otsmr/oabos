
class Dialog{
    constructor(text, ok, cancel, btn2 ){
        this.text = text;
        
        this.callBack_ok = ok;
        this.callBack_btn2 = btn2 ;
        this.callBack_cancel = cancel;
        this.create();
        return this;
    }

    create(){
        var $input1 = $("<input>"), $input2 = $("<input>");

        this.$overlay = $('<div>').addClass("dialog_overlay");

        this.$dialog = $("<div>").addClass("dialog");
        if(this.text.title) this.$dialog.append($("<h3>").text(this.text.title));

        if(this.text.desc) this.$dialog.append($("<p>").text(this.text.desc));


        if(this.text.input1) $input1 = $(this.text.input1).appendTo(this.$dialog);
        if(this.text.input2) $input2 = $(this.text.input2).appendTo(this.$dialog);
        

        
        var ok = $('<button>').addClass("left").text(this.text.ok).click((e)=>{
            this.callBack_ok($input1.val(), $input2.val());
            this.remove();
        });

        if(this.text.btn2){
            var btn2 = $('<button>').addClass("right").text(this.text.btn2).click(()=>{
                this.remove();
                if(this.callBack_btn2) this.callBack_btn2();
            });
        }
        if(this.text.cancel){
            var cancel = $('<button>').addClass("right").text(this.text.cancel).click(()=>{
                this.remove();
                if(this.callBack_cancel) this.callBack_cancel();
            });
        }

        $('body').append(this.$overlay, this.$dialog.append(ok, cancel, btn2));
        if($input1) $input1.focus();
    }

    remove(){
        this.$dialog.remove();
        this.$overlay.remove();
    }
}
