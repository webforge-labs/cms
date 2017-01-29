/*! UIkit 3.0.0-beta.5 | http://www.getuikit.com | (c) 2014 - 2016 YOOtheme | MIT License */

(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? factory(require('uikit')) :
    typeof define === 'function' && define.amd ? define(['uikit'], factory) :
    (factory(global.UIkit));
}(this, (function (uikit) { 'use strict';

var $ = uikit.util.$;
var ajax = uikit.util.ajax;
var on = uikit.util.on;

UIkit.component('upload', {

    props: {
        allow: String,
        clsDragover: String,
        concurrent: Number,
        dataType: String,
        mime: String,
        msgInvalidMime: String,
        msgInvalidName: String,
        multiple: Boolean,
        name: String,
        params: Object,
        type: String,
        url: String
    },

    defaults: {
        allow: false,
        clsDragover: 'uk-dragover',
        concurrent: 1,
        dataType: undefined,
        mime: false,
        msgInvalidMime: 'Invalid File Type: %s',
        msgInvalidName: 'Invalid File Name: %s',
        multiple: false,
        name: 'files[]',
        params: {},
        type: 'POST',
        url: '',
        abort: null,
        beforeAll: null,
        beforeSend: null,
        complete: null,
        completeAll: null,
        error: null,
        fail: function fail(msg) {
            alert(msg);
        },
        load: null,
        loadEnd: null,
        loadStart: null,
        progress: null
    },

    events: {

        change: function change(e) {

            if (!$(e.target).is('input[type="file"]')) {
                return;
            }

            e.preventDefault();

            if (e.target.files) {
                this.upload(e.target.files);
            }

            e.target.value = '';
        },

        drop: function drop(e) {
            e.preventDefault();
            e.stopPropagation();

            var transfer = e.originalEvent.dataTransfer;

            if (!transfer || !transfer.files) {
                return;
            }

            this.$el.removeClass(this.clsDragover);

            this.upload(transfer.files);
        },

        dragenter: function dragenter(e) {
            e.preventDefault();
            e.stopPropagation();
        },

        dragover: function dragover(e) {
            e.preventDefault();
            e.stopPropagation();
            this.$el.addClass(this.clsDragover);
        },

        dragleave: function dragleave(e) {
            e.preventDefault();
            e.stopPropagation();
            this.$el.removeClass(this.clsDragover);
        }

    },

    methods: {

        upload: function upload(files) {
            var this$1 = this;


            if (!files.length) {
                return;
            }

            this.$el.trigger('upload', [files]);

            for (var i = 0; i < files.length; i++) {

                if (this$1.allow) {
                    if (!match(this$1.allow, files[i].name)) {
                        this$1.fail(this$1.msgInvalidName.replace(/%s/, this$1.allow));
                        return;
                    }
                }

                if (this$1.mime) {
                    if (!match(this$1.mime, files[i].type)) {
                        this$1.fail(this$1.msgInvalidMime.replace(/%s/, this$1.mime));
                        return;
                    }
                }

            }

            if (!this.multiple) {
                files = [files[0]];
            }

            this.beforeAll && this.beforeAll(this, files);

            var chunks = chunk(files, this.concurrent),
                upload = function (files) {

                    var data = new FormData();

                    files.forEach(function (file) { return data.append(this$1.name, file); });

                    for (var key in this$1.params) {
                        data.append(key, this$1.params[key]);
                    }

                    ajax({
                        data: data,
                        url: this$1.url,
                        type: this$1.type,
                        dataType: this$1.dataType,
                        beforeSend: this$1.beforeSend,
                        complete: [this$1.complete, function (xhr, status) {
                            if (chunks.length) {
                                upload(chunks.shift());
                            } else {
                                this$1.completeAll && this$1.completeAll(xhr);
                            }

                            if (status === 'abort') {
                                this$1.abort && this$1.abort(xhr);
                            }
                        }],
                        cache: false,
                        contentType: false,
                        processData: false,
                        xhr: function () {
                            var xhr = $.ajaxSettings.xhr();
                            xhr.upload && this$1.progress && on(xhr.upload, 'progress', this$1.progress);
                            ['loadStart', 'load', 'loadEnd', 'error', 'abort'].forEach(function (type) { return this$1[type] && on(xhr, type.toLowerCase(), this$1[type]); });
                            return xhr;
                        }
                    })

                };

            upload(chunks.shift());

        }

    }

});

function match(pattern, path) {
    return path.match(new RegExp(("^" + (pattern.replace(/\//g, '\\/').replace(/\*\*/g, '(\\/[^\\/]+)*').replace(/\*/g, '[^\\/]+').replace(/((?!\\))\?/g, '$1.')) + "$"), 'i'));
}

function chunk(files, size) {
    var chunks = [];
    for (var i = 0; i < files.length; i += size) {
        var chunk = [];
        for (var j = 0; j < size; j++) {
            chunk.push(files[i+j]);
        }
        chunks.push(chunk);
    }
    return chunks;
}

})));