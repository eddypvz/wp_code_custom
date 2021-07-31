
const WPCC_builder = function() {

    const self = this;
    const $ = jQuery;

    this.fileType = function(wp_attachment) {

        const fileUrl = wp_attachment.url;

        const filetype = {};
        filetype["type"] = "no_supported";
        filetype["url"] = (typeof wp_attachment.url !== "undefined") ? wp_attachment.url : null;
        filetype["ext"] = filetype["url"].substr(filetype["url"].lastIndexOf('.') + 1);
        filetype["name"] = (typeof wp_attachment.filename !== "undefined") ? wp_attachment.filename : null;


        const imageExtensions = [
            "jpg", "jpeg", "jpe", "jif", "jfif", "jfi", "png", "webp", "bmp", "dib","jp2", "svg", "gif", "tiff", "tif", "raw","svgz"
        ];

        const videoExtensions = [
            "mp4", "webm", "ogg", "avi"
        ];

        const audioExtensions = [
            "mp3", "aac", "midi"
        ];

        const fileExtensions = [
            "pdf", "txt", "eps", "ai", "txt", "doc", "zip",
        ];

        if (imageExtensions.includes(filetype["ext"])) {
            filetype["type"] = "image";
        }
        else if (videoExtensions.includes(filetype["ext"])) {
            filetype["type"] = "video";
        }
        else if (audioExtensions.includes(filetype["ext"])) {
            filetype["type"] = "audio";
        }
        else if (fileExtensions.includes(filetype["ext"])) {
            filetype["type"] = "file";
        }

        return filetype;
    };

    this.refreshEvents = function() {

        /*EVENTS REPEATER*/
        $(".repeater_delete").unbind("click").click(function() {
            $(this).parent().parent().parent().remove();
        });

        $(".WPCC_repeater_add").unbind("click").click(function() {

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
            const video = mediaContainer.find("video");
            const input = mediaContainer.find("input[type='hidden']");
            const filePreview = mediaContainer.find(".preview_file");
            const imagePath = img.attr("data-images");

            //If is select action
            if(action === "select") {
                let urlAttach = wp.media.editor.send.attachment;

                // Get wp.media.editor
                wp.media.editor.send.attachment = function(props, attachment){

                    const filetype = self.fileType(attachment);

                    if (filetype["type"] === "image") {
                        img.attr("src", filetype["url"]).show();
                        video.hide();
                        img.show();
                        filePreview.hide();
                    }
                    else if (filetype["type"] === "video") {
                        // check extension and hide previews
                        video.append('<source src="'+filetype["url"]+'#t=0.5" type="video/mp4">').show();
                        video.show();
                        img.hide();
                        filePreview.hide();
                    }
                    else if (filetype["type"] === "file") {
                        img.attr("src", imagePath+"file-extensions/"+filetype["ext"]+".png").show();
                        filePreview.find(".filenamePreviewLink").attr("href", filetype["url"]).html(filetype["name"]);
                        video.hide();
                        img.show();
                        filePreview.show();
                    }

                    input.val(filetype["url"]);
                    wp.media.editor.send.attachment = urlAttach;
                };
                wp.media.editor.open();
            }
            else{
                //if is delete action
                input.val("");
                img.attr("src", imagePath+"noimage.png").show();
                video.html('').hide();

                //clear file link
                filePreview.hide();
            }
        });

        //TINY MCE for field_text_editor
        $(".WPCC_Field_Editor").each( function(a, b) {

            const slug = $(b).attr("data-slug");
            const is_editor = $(b).attr("data-editor");

            // If the wp.editor library exists and the editor is not instanced
            if (parseInt(is_editor) !== 1) {

                const WP_editor = (typeof wp.editor.initialize !== "undefined") ? wp.editor : (typeof wp.oldEditor.initialize !== "undefined") ? wp.oldEditor : false;
                if(WP_editor) {
                    WP_editor.initialize(slug, {
                        mediaButtons: true,
                        tinymce:      {
                            toolbar1: 'formatselect, bold, italic, bullist, numlist, link, blockquote, alignleft, aligncenter,alignright,strikethrough,hr,forecolor,pastetext,removeformat,codeformat,undo,redo'
                        },
                        quicktags: {
                            buttons: 'strong,em,link,block,del,ins,img,ul,ol,li,code,more,close,dfw'
                        }

                    });
                    $(b).attr("data-editor", 1);
                }
            }
        });

        /* COLOR PICKER */
        $(".WPCC_color_picker").each(function(a, b) {

            const pickerValue = $(this).find(".picker-value");
            const pickerID = $(this).attr("data-picker");
            const actualColor = pickerValue.val();

            // if the picker not has instance
            if ($("#"+pickerID).length > 0) {
                let defaultColor = "#51545a";
                if (actualColor !== "") {
                    defaultColor = actualColor;
                }

                const tmpPicker = Pickr.create({
                    el: "#"+pickerID,
                    theme: 'classic', // or 'monolith', or 'nano'
                    default: defaultColor,
                    defaultRepresentation: 'RGBA',
                    swatches: [
                        'rgba(244, 67, 54, 1)',
                        'rgba(233, 30, 99, 1)',
                        'rgba(156, 39, 176, 1)',
                        'rgba(103, 58, 183, 1)',
                        'rgba(63, 81, 181, 1)',
                        'rgba(33, 150, 243, 1)',
                        'rgba(3, 169, 244, 1)',
                        'rgba(0, 188, 212, 1)',
                        'rgba(0, 150, 136, 1)',
                        'rgba(76, 175, 80, 1)',
                        'rgba(139, 195, 74, 1)',
                        'rgba(205, 220, 57, 1)',
                        'rgba(255, 235, 59, 1)',
                        'rgba(255, 193, 7, 1)'
                    ],
                    components: {
                        preview: true,
                        opacity: true,
                        hue: true,
                        interaction: {
                            hex: false,
                            rgba: false,
                            hsla: false,
                            hsva: false,
                            cmyk: false,
                            input: true,
                            clear: true,
                            save: true
                        }
                    },
                    position: 'right-end',
                }).on('save', (color, instance) => {
                    let saveColor = "";
                    if (color !== null) {
                        saveColor = color.toRGBA().toString(3)
                    }
                    pickerValue.val(saveColor);
                    tmpPicker.hide()
                });
            }
        });

        /* Autocomplete */
        $('.WPCC_field_autocomplete').each(function (a ,b) {
            const slugField = $(b).attr('data-slug');
            const initialValue = $(b).val();
            let startValue = 1;
            const autoCompleteJS = new autoComplete({
                selector: '#'+$(b).attr('id'),
                placeHolder: "Escribe aquí para buscar",
                data: {
                    src: async (query) => {
                        try {
                            // Fetch Data from external Source
                            const source = await fetch(ajaxurl+'?action=wpcc_builder_ajax_source&field='+slugField+'&s='+query+'&i='+startValue);
                            return await source.json();
                        } catch (error) {
                            return error;
                        }
                    },
                    // Data 'Object' key to be searched
                    keys: ["value", "key"]
                },
                resultItem: {
                    element: (item, data) => {
                        item.style = "display: flex; justify-content: space-between;";
                        // Modify Results Item Content
                        item.innerHTML = `<span style="text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">
                                            ${data.value.value}
                                          </span>
                                          <span style="display: flex; align-items: center; font-size: 13px; font-weight: 100; text-transform: uppercase; color: rgba(0,0,0,.2);">
                                            (${data.value.key})
                                          </span>`;
                    },
                    highlight: true
                },
                resultsList: {
                    element: (list, data) => {
                        const info = document.createElement("p");
                        if (data.results.length > 0) {
                            info.innerHTML = `Mostrando <strong>${data.results.length}</strong> de <strong>${data.matches.length}</strong> resultados`;
                        } else {
                            info.innerHTML = `Encontrados <strong>${data.matches.length}</strong> resultados con <strong>"${data.query}"</strong>`;
                        }
                        list.prepend(info);
                    },
                    noResults: true,
                    maxResults: 15,
                    tabSelect: true
                },
                events: {
                    input: {
                        focus: () => {
                            if (autoCompleteJS.input.value.length) autoCompleteJS.start();
                        }
                    }
                }
            });

            autoCompleteJS.input.addEventListener("selection", function (event) {
                startValue = 0;
                const feedback = event.detail;
                autoCompleteJS.input.blur();
                autoCompleteJS.input.value = feedback.selection.value.value;
                $(autoCompleteJS.input).parent().parent().find('.WPCC_field_autocomplete_value').val(feedback.selection.value.key);
            });

            // on load results, select first
            autoCompleteJS.input.addEventListener("open", function (event) {
                if (initialValue !== '' && startValue) {
                    autoCompleteJS.select(0);
                }
            });

            // on autocomplete start, search the selected value
            if (initialValue !== '' && startValue) {
                autoCompleteJS.start(initialValue);
            }
        });

        /*Chosen*/
        $(".WPCC_field_chosen_select").chosen();
    }
};

jQuery(document).ready(function() {
    const wpcc = new WPCC_builder();
    wpcc.refreshEvents();
});

