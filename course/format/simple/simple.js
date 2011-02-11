YAHOO.namespace('moodle');

YAHOO.moodle.simpleformat =  function() {

    var topics;
    var current;
    var currentindex;
    var $c = YAHOO.util.Dom.getElementsByClassName;


    return {
        init: function() {
            topics = $c('section', 'li', document.getElementById('middle-column'));
            currentindex = parseInt(YAHOO.util.Cookie.getSub('simpleformat', courseid.toString()));
            showblocks= parseInt(YAHOO.util.Cookie.getSub('simpleformat-blocks', courseid.toString()));

            if (!topics[currentindex]) {
                currentindex = 0;
            }

            var lasttopic =  null;

            for(var i in topics) {
                i = parseInt(i);
                // Hide all but the current link
                if (currentindex == i) {
                    current = topics[i];
                }
                else if (!currentindex && YAHOO.util.Dom.hasClass(topics[i], 'current')) {
                    current = topics[i];
                    currentindex = i;
                }
                else {
                    topics[i].style.display = 'none';
                }

                // Remove border
               topics[i].style.border = 'none';

                if (lasttopic) {
                    // Insert previous link

                    // Find  name of next topic
                    var linkname = $c('sectionname', 'h3', lasttopic)[0];

                    var previouslink = document.createElement('div');
                    YAHOO.util.Dom.addClass(previouslink, 'simplebackbutton-text');

                    if (linkname && linkname.innerHTML) {
                       previouslink.innerHTML = linkname.innerHTML;
                    }


                    topics[i].appendChild(YAHOO.moodle.simpleformat.createbackbutton('next_'+i, strprevious, YAHOO.moodle.simpleformat.showprevious));
                    topics[i].appendChild(previouslink);

                }

                if (topics[i+1]) {
                    // Find  name of next topic
                    var linkname = $c('sectionname', 'h3', topics[i+1])[0];

                    // Insert next link
                    var nextlink = document.createElement('div');
                    nextlink.id = 'next_'+i;
                    YAHOO.util.Dom.addClass(nextlink, 'simpleforwardbutton-text');

                    if (linkname && linkname.innerHTML) {
                       nextlink.innerHTML = linkname.innerHTML;
                    }

                    topics[i].appendChild(YAHOO.moodle.simpleformat.createforwardbutton('next_'+i, strnext, YAHOO.moodle.simpleformat.shownext));
                    topics[i].appendChild(nextlink);

                }

                var clearer = document.createElement('div');
                YAHOO.util.Dom.addClass(clearer, 'clearer');
                topics[i].appendChild(clearer);

                lasttopic = topics[i];
            }

            // Hide blocks
            var showhideblocks = document.createElement('input');
            showhideblocks.type = 'button';
            showhideblocks.id = 'showhideblocks';

            YAHOO.util.Dom.addClass(showhideblocks, 'navbutton');

            var navbar = $c('nav-effect')[0];
            navbar.appendChild(showhideblocks);

            if (!isediting && !showblocks) {
                YAHOO.moodle.simpleformat.hideblocks();
            } else {
                YAHOO.moodle.simpleformat.showblocks();
            }

        },

        shownext: function(e) {
            YAHOO.util.Cookie.setSub("simpleformat", courseid.toString(), currentindex+1);
            YAHOO.util.Event.preventDefault(e);
            topics[currentindex].style.display = 'none';
            topics[currentindex+1].style.display = 'block';
            currentindex++;
        },

        showprevious: function(e) {
            YAHOO.util.Cookie.setSub("simpleformat", courseid.toString(), currentindex-1);
            YAHOO.util.Event.preventDefault(e);
            topics[currentindex-1].style.display = 'block';
            topics[currentindex].style.display = 'none';
            currentindex--;
        },

        showall: function(e) {
            YAHOO.util.Event.preventDefault(e);
            for(var i in topics) {
                topics[i].style.display = 'block';
            }
        },

        createforwardbutton: function(id, name, callback) {

            var button = document.createElement('div');
            YAHOO.util.Dom.addClass(button, 'simplebutton');
            YAHOO.util.Dom.addClass(button, 'simpleforwardbutton');

            var bleft = document.createElement('div');
            YAHOO.util.Dom.addClass(bleft, 'simplebutton-right');

            var bright = document.createElement('div');
            YAHOO.util.Dom.addClass(bright, 'simplebutton-title-left');

            var link = document.createElement('a');
            link.id = id;
            link.innerHTML = name;
            link.href= '#';
            YAHOO.util.Dom.addClass(link, 'arrow-right');
            YAHOO.util.Event.addListener(button, 'click', callback);

            button.appendChild(bleft);
            bleft.appendChild(bright);
            bright.appendChild(link);

            return button;
        },

        createbackbutton: function(id, name, callback) {

            var button = document.createElement('div');
            YAHOO.util.Dom.addClass(button, 'simplebutton');
            YAHOO.util.Dom.addClass(button, 'simplebackbutton');

            var bleft = document.createElement('div');
            YAHOO.util.Dom.addClass(bleft, 'simplebutton-left');

            var bright = document.createElement('div');
            YAHOO.util.Dom.addClass(bright, 'simplebutton-title-right');

            var link = document.createElement('a');
            link.id = id;
            link.innerHTML = name;
            link.href= '#';
            YAHOO.util.Dom.addClass(link, 'arrow-left');
            YAHOO.util.Event.addListener(button, 'click', callback);

            button.appendChild(bleft);
            bleft.appendChild(bright);
            bright.appendChild(link);

            return button;
        },

        hideblocks: function() {
//            var leftcol = document.getElementById('left-column');
//            var rightcol = document.getElementById('right-column');
//            var middlecol = document.getElementById('middle-column');
            var showhide = document.getElementById('showhideblocks');

            showhide.value = 'Show Blocks';
            YAHOO.util.Event.removeListener(showhide, 'click');
            YAHOO.util.Event.addListener(showhide, 'click', YAHOO.moodle.simpleformat.showblocks);
            YAHOO.util.Cookie.setSub('simpleformat-blocks', courseid.toString(), 0);

//            if (leftcol) {
//                leftcol.style.display = 'none';
//            }
//            if (rightcol) {
//                rightcol.style.display = 'none';
//            }
//            middlecol.style.margin = '0 1em';
            YUI().use('node', function (Y){
                Y.all('.block-region').setStyle('display','none');
            });
        },

        showblocks: function() {
//            var leftcol = document.getElementById('left-column');
//            var rightcol = document.getElementById('right-column');
//            var middlecol = document.getElementById('middle-column');
            var showhide = document.getElementById('showhideblocks');

            showhide.value = strhideblocks;
            YAHOO.util.Event.removeListener(showhide, 'click');
            YAHOO.util.Event.addListener(showhide, 'click', YAHOO.moodle.simpleformat.hideblocks);
            YAHOO.util.Cookie.setSub('simpleformat-blocks', courseid.toString(), 1);

//            if (leftcol) {
//                leftcol.style.display = 'block';
//            }
//            if (rightcol) {
//                rightcol.style.display = 'block';
//            }
//            middlecol.style.margin = '0 16em';
            YUI().use('node', function (Y){
                Y.all('.block-region').setStyle('display','block');
            });
        }

    }


}();

YAHOO.util.Event.onDOMReady(YAHOO.moodle.simpleformat.init);
