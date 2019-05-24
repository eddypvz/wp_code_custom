const WPCC_builder = function() {

    const self = this;
    const $ = jQuery;

    this.refreshEvents = function() {

        /*EVENTS REPEATER*/
        $(".repeater_delete").unbind("click").click(function() {
            $(this).parent().parent().parent().remove();
        });

        $(".repeater_add").unbind("click").click(function() {

            let objectRepeater = this;
            let obj = $(this);

            //counter
            let counter = obj.parent().parent().find(".WPCC_group_item").size();
            counter++;

            //others
            let slug = obj.attr("data-slug");
            let type = obj.attr("data-type");

            $.ajax({
                url: ajaxurl,
                data: {
                    action: "wpcc_repeat_add",
                    add_from: slug,
                    type: type,
                    repeat: counter,
                },
                method: "GET"
            })
            .done(function(data) {

                //set counter for repeater
                obj.attr("data-counter", counter);

                // append data
                $(objectRepeater).parent().parent().find(".WPCC_group_content").append(data);

                // Refresh events
                self.refreshEvents();

            })
            .fail(function() {
                console.log("error al dibujar");
            });
        });

        /* MEDIA FIELD */
        $(".WPCC_Field_Media_Action").unbind("click").click(function() {

            const action = $(this).attr("data-action");
            const mediaContainer = $(this).parent().parent();
            const img = mediaContainer.find("img");
            const input = mediaContainer.find("input[type='hidden']");

            //If is select action
            if(action === "select") {
                let urlAttach = wp.media.editor.send.attachment;

                // Get wp.media.editor
                wp.media.editor.send.attachment = function(props, attachment){
                    img.attr("src", attachment.url);
                    input.val(attachment.url);
                    wp.media.editor.send.attachment = urlAttach;
                };
                wp.media.editor.open();
            }
            else{
                //if is delete action
                input.val("");
                img.attr("src", img.attr("data-none"));
            }
        });

        //TINY MCE for field_text_editor
        $(".WPCC_Field_Editor").each( function(a, b) {

            const slug = $(b).attr("data-slug");
            const is_editor = $(b).attr("data-editor");

            // If the wp.editor library exists and the editor is not instanced
            if(typeof wp.editor.initialize !== "undefined" && parseInt(is_editor) !== 1) {
                wp.editor.initialize(slug, {
                    mediaButtons: true,
                    tinymce: true,
                    quicktags: true,
                });
                $(b).attr("data-editor", 1);
            }
            else {
                if(typeof  tinyMCEPreInit.mceInit.content != "undefined") {
                    tinymce.init(tinyMCEPreInit.mceInit.content);
                }
                tinyMCE.execCommand('mceAddEditor', true, slug);
                if(typeof QTags.instances[0] != "undefined" && QTags.instances[0])QTags.instances[0] = false;
                quicktags({id : slug});
                $(b).find(".wp-editor-wrap").removeClass("html-active").addClass("tmce-active");
            }
        });
    }
};

jQuery(document).ready(function() {
    const wpcc = new WPCC_builder();
    wpcc.refreshEvents();
});

