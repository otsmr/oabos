class ParseRequest{

    parse(r, call, failCall){

        try {
            r = JSON.parse(r);

            if(typeof r.error !== "undefined" && typeof r.ok === "undefined") {
                if(failCall) failCall(r.error);
                else{
                    switch (r.error) {
                        default:
                            // notification.newNoti({text: l.t("connection.failed", this.name, r.error), icon: "warn", quelle: "connections.js"});
                        break;
                    }
                }
            } 
            else {
                if(r.ok === "true") r.ok = true;
                if(r.ok === "false") r.ok = false;
                if(call) call(r.ok);
                else return r.ok;
            } 
        
        } catch (e) {
            console.log(e);
            // notification.newNoti({text: "Bei der Anfrage ist ein unerwarteter Fehler Aufgetreten.", icon: "warn", quelle: "connections.js"});
        }

        return false;
    }
}