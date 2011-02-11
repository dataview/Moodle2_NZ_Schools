var $ = YAHOO.util.Dom.get;

function updateShortName() {
    var shortname = $('id_shortname');
    var sitename = $('id_sitename');

    if (shortname.value == '' && sitename.value != '') {
        var words = sitename.value.split(" ");

        for(var i in words) {
            if (words[i][0]) {
                shortname.value += words[i][0].toUpperCase();
            }
        }
    }
    return true;
}

//function init(){
//
//   document.body.className += ' yui-skin-sam';
//    var onFileSelect = function(e){
//        YAHOO.util.Connect.setForm('mform1', true);
//
//        var uploadHandler = {
//            upload: function(o) {
//                var logo = $('logoimage');
//                logo.src= o.responseText;
//            }
//        };
//        YAHOO.util.Connect.asyncRequest('POST', 'logo_upload.php', uploadHandler);
//    };


//    onColourChange = function(e) {
//        var colour = false;
//        var colour1 = $('id_colour1').value;
//        var colour2 = $('id_colour2').value;
//        var colour3 = $('id_colour3').value;
//        var plainbg = $('id_plainbg').checked;
//
//        // Reload existing css file
//        YAHOO.util.Get.css(dynamic_css+'?colour1='+colour1+'&colour2='+colour2+'&colour3='+colour3+'&plainbg='+plainbg);
//    };

//    YAHOO.util.Event.on('id_logo', 'change', onFileSelect);
//    for (var c=1;c<=3;c++) {
//        YAHOO.util.Event.on('id_colour'+c, 'change', onColourChange);
//    }
//
//    YAHOO.util.Event.on('id_plainbg', 'change', onColourChange);
//}

// Color picker

//YAHOO.namespace("moodle.colorpicker");

//create a new object for this module:
//YAHOO.moodle.colorpicker.inDialog = function() {
//
//    //Some shortcuts
//    var Event=YAHOO.util.Event,
//        Dom=YAHOO.util.Dom,
//        lang=YAHOO.lang;
//
//    return {
//
//        init: function() {
//
//            // Instantiate the Dialog
//            this.dialog = new YAHOO.widget.Dialog("yui-picker-panel", {
//                width : "350px",
//                fixedcenter : true,
//                visible : false,
//                constraintoviewport : true,
//                buttons : [ { text:"Submit", handler:this.handleSubmit, isDefault:true },
//                            { text:"Cancel", handler:this.handleCancel } ]
//             });
//             this.dialog.renderEvent.subscribe(function() {
//				if (!this.picker) { //make sure that we haven't already created our Color Picker
//					this.picker = new YAHOO.widget.ColorPicker("yui-picker", {
//						container: this.dialog,
//						images: {
//                            PICKER_THUMB: "../lib/yui/colorpicker/assets/picker_thumb.png",
//                            HUE_THUMB: "../lib/yui/colorpicker/assets/hue_thumb.png"
//						},
//						showhexcontrols: true,
//                        showwebsafe: false
//					});
//
//					//listen to rgbChange to be notified about new values
//					this.picker.on("rgbChange", function(o) {
//					});
//				}
//			});
//
//            this.dialog.setElement = function(e) {
//                this.target = YAHOO.util.Event.getTarget(e);
//                if (this.target.value) {
//                    this.picker.setValue(YAHOO.util.Color.hex2rgb(this.target.value));
//                }
//                this.show();
//            }
//
//            this.dialog.validate = function() {
//				return true;
//            };
//
//            // Wire up the success and failure handlers
//            this.dialog.callback = { success: this.handleSuccess, thisfailure: this.handleFailure };
//            this.dialog.render();
//
//
//            for (var c=1;c<=3;c++) {
//                Event.on("id_colour"+c, "click", this.dialog.setElement, this.dialog, true);
//            }
//
//		},
//
//		handleSubmit: function() {
//            this.target.value = this.picker.get("hex");
//            onColourChange();
//            this.hide();
//		},
//
//		handleCancel: function() {
//			this.cancel();
//		},
//
//		handleSuccess: function(o) {
//		},
//
//		handleFailure: function(o) {
//			alert("Response object:" + lang.dump(o), "error", "example");
//		}
//
//	}
//
//
//}();

//YAHOO.util.Event.on(window, 'load', init);
//YAHOO.util.Event.onDOMReady(YAHOO.moodle.colorpicker.inDialog.init, YAHOO.moodle.colorpicker.inDialog, true);
