M.core_rating={

    Y : null,
    transaction : [],

    init : function(Y){
        this.Y = Y;
        Y.all('select.postratingmenu').each(this.attach_rating_events, this);

        //hide the submit buttons
        this.Y.all('input.postratingmenusubmit').setStyle('display', 'none');
    },

    attach_rating_events : function(selectnode) {
        selectnode.on('change', this.submit_rating, this, selectnode);
    },

    submit_rating : function(e, selectnode){
        var theinputs = selectnode.ancestor('form').all('.ratinginput');
        var thedata = [];

        var inputssize = theinputs.size();
        for ( var i=0; i<inputssize; i++ )
        {
            if(theinputs.item(i).get("name")!="returnurl") {//dont include return url for ajax requests
                thedata[theinputs.item(i).get("name")] = theinputs.item(i).get("value");
            }
        }

        this.Y.io.queue.stop();
        this.transaction.push({transaction:this.Y.io.queue(M.cfg.wwwroot+'/rating/rate_ajax.php', {
            method : 'POST',
            data : build_querystring(thedata),
            on : {
                complete : function(tid, outcome, args) {
                    try {
                        if (!outcome) {
                            alert('IO FATAL');
                            return false;
                        }

                        var data = this.Y.JSON.parse(outcome.responseText);
                        if (data.success){
                            //if the user has access to the aggregate then update it
                            if (data.itemid) { //do not test data.aggregate or data.count otherwise it doesn't refresh value=0 or no value
                                var itemid = data.itemid;

                                var node = this.Y.one('#ratingaggregate'+itemid);
                                node.set('innerHTML',data.aggregate);

                                //empty the count value if no ratings
                                var node = this.Y.one('#ratingcount'+itemid);
                                if (data.count > 0) {
                                    node.set('innerHTML',"("+data.count+")");
                                } else {
                                    node.set('innerHTML',"");
                                }
                            }
                            return true;
                        }
                        else if (data.error){
                            alert(data.error);
                        }
                    } catch(e) {
                        alert(e.message+" "+outcome.responseText);
                    }
                    return false;
                }
            },
            context : this,
            arguments : {
                //query : this.query.get('value')
            }
        }),complete:false,outcome:null});
        this.Y.io.queue.start();
    }
};