/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2018 TwelveTone LLC
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

function onMediaAction_MediaReplace(actionId, mediaName, mediaElement) {
    let form = document.querySelector('form[id="replace-media"]');
    if (!form) {
        form = $("<form id='replace-media' action='MediaReplace' method='post'>" +
            "<input type='file' style='visibility: hidden; position: absolute; top: 0px; left: 0px; height: 0px; width: 0px;'></input>" +
            "</form>")[0];
        document.body.appendChild(form);

        let input = form.querySelector('input[type=file]');

        input.addEventListener('change', function () {
            if (input.files.length !== 1) {
                return;
            }

            let file = input.files[0];
            let data = new FormData();
            data.append('mediaupload', file, file.name);

            let xhr = new XMLHttpRequest();
            xhr.open("POST", GravAdmin.config.base_url_relative + '/admin-media-replace/replace', true);
            // xhr.setRequestHeader("X_FILENAME", file.name);
            // Grav is stripping out X_ from $_SERVER
            xhr.setRequestHeader("X-MEDIA-NEW-FILENAME", file.name);
            xhr.setRequestHeader("X-MEDIA-ROUTE", '/' + GravAdmin.config.route);
            xhr.setRequestHeader("X-MEDIA-FILENAME", mediaName);

            data.append("media-new-filename", file.name);
            data.append("media-route", '/' + GravAdmin.config.route);
            data.append("media-filename", mediaName);

            // This does not work
            xhr.onload = function () {
                //get response and show the uploading status
                if (xhr.status === 200) {
                    let response = JSON.parse(xhr.responseText);
                    if (response.error) {
                        alert(response.error);
                    }
                    else if (response.thumbnail) {
                        let img = mediaElement.querySelector('img');
                        if (!img) {
                            return;
                        }
                        img.src = response.thumbnail + "?refresh=" + new Date().getTime();
                    } else {
                        location.reload();
                    }
                }
            };

            xhr.send(data);
        });
    }
    let input = form.querySelector('input[type=file]');
    input.click();
}