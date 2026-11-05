<?php
include_once '../helpers.php';

// Version de Swagger UI (dernière publication : https://www.npmjs.com/package/swagger-ui-dist)
$swaggerUiVersion = "5.34.1";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Documentation API - <?php echo htmlspecialchars(config('app_name')); ?></title>
    <link rel="stylesheet" type="text/css" id="swagger-css" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@<?php echo $swaggerUiVersion; ?>/swagger-ui.css" />
    <script>
        // Repli CSS si jsDelivr est indisponible
        document.getElementById('swagger-css').onerror = function () {
            this.onerror = null;
            this.href = 'https://unpkg.com/swagger-ui-dist@<?php echo $swaggerUiVersion; ?>/swagger-ui.css';
        };
    </script>
    <style>
        html { box-sizing: border-box; overflow: -moz-scrollbars-vertical; overflow-y: scroll; }
        *, *:before, *:after { box-sizing: inherit; }
        body { margin: 0; background: #fafafa; }
        .topbar { display: none; }
        #swagger-error { display: none; max-width: 720px; margin: 2rem auto; padding: 1rem 1.25rem; border: 1px solid #f0c36d; background: #fff8e5; color: #5c4400; font-family: system-ui, sans-serif; border-radius: 6px; }
        #swagger-error code { background: rgba(0,0,0,.06); padding: .1rem .3rem; border-radius: 3px; }
        #appid-autofill-hint { display: none; padding: .6rem 1rem; background: #e8f5e9; color: #1b5e20; border-bottom: 1px solid #a5d6a7; font-family: system-ui, sans-serif; font-size: .9rem; }
        /* Exemples cURL */
        .swagger-ui pre.curl-example { position: relative; padding-right: 5rem !important; }
        .swagger-ui pre code { font-weight: 400 !important; font-size: 13px !important; line-height: 1.5; }
        .curl-copy-btn { position: absolute; top: .4rem; right: .4rem; padding: .2rem .55rem; font-size: 12px; font-family: system-ui, sans-serif; color: #3b4151; background: #fff; border: 1px solid #c9ced6; border-radius: 4px; cursor: pointer; opacity: .9; }
        .curl-copy-btn:hover { background: #f0f2f5; }
        .curl-copy-btn.is-copied { color: #1b5e20; border-color: #a5d6a7; background: #e8f5e9; }
        .curl-cmd { color: #0550ae; font-weight: 600; }
        .curl-method { color: #cf222e; font-weight: 600; }
        .curl-flag { color: #8250df; }
        .curl-url { color: #0a7d33; }
        .curl-string { color: #0a3069; }
        .curl-var { color: #b35900; font-weight: 600; }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <div id="swagger-error">
        <strong>Impossible de charger Swagger UI.</strong><br>
        Vérifiez votre connexion ou un éventuel bloqueur de scripts, puis
        <a href="">rechargez la page</a>. La spécification reste accessible
        directement via <a href="/api/doc">/api/doc</a>.
    </div>

    <script>
    (function () {
        var DOC_URL = <?php echo json_encode("/api/doc"); ?>;
        var VERSION = <?php echo json_encode($swaggerUiVersion); ?>;
        var CDNS = [
            {
                bundle: "https://cdn.jsdelivr.net/npm/swagger-ui-dist@" + VERSION + "/swagger-ui-bundle.js",
                preset: "https://cdn.jsdelivr.net/npm/swagger-ui-dist@" + VERSION + "/swagger-ui-standalone-preset.js"
            },
            {
                bundle: "https://unpkg.com/swagger-ui-dist@" + VERSION + "/swagger-ui-bundle.js",
                preset: "https://unpkg.com/swagger-ui-dist@" + VERSION + "/swagger-ui-standalone-preset.js"
            },
            {
                // Dernier recours : dernière 5.x disponible sur jsDelivr
                bundle: "https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-bundle.js",
                preset: "https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-standalone-preset.js"
            }
        ];

        function showError(error) {
            var el = document.getElementById("swagger-error");
            if (el) {
                el.style.display = "block";
            }
            if (window.console && error) {
                console.error(error);
            }
        }

        function loadScript(src) {
            return new Promise(function (resolve, reject) {
                var script = document.createElement("script");
                script.src = src;
                script.charset = "UTF-8";
                script.async = false;
                script.onload = function () { resolve(src); };
                script.onerror = function () {
                    reject(new Error("Échec du chargement de " + src));
                };
                document.body.appendChild(script);
            });
        }

        function loadBundle(index) {
            if (index >= CDNS.length) {
                return Promise.reject(new Error("Swagger UI bundle introuvable sur tous les CDN"));
            }
            return loadScript(CDNS[index].bundle).catch(function () {
                return loadBundle(index + 1);
            });
        }

        // Le preset standalone est optionnel : s'il échoue, on utilise le BaseLayout.
        function loadPreset(index) {
            if (index >= CDNS.length) {
                return Promise.resolve(false);
            }
            return loadScript(CDNS[index].preset)
                .then(function () { return true; })
                .catch(function () { return loadPreset(index + 1); });
        }

        function init(hasPreset) {
            var presetAvailable =
                hasPreset && typeof window.SwaggerUIStandalonePreset !== "undefined";
            var presets = [SwaggerUIBundle.presets.apis];

            if (presetAvailable) {
                presets.push(window.SwaggerUIStandalonePreset);
            }

            window.ui = SwaggerUIBundle({
                url: DOC_URL,
                dom_id: "#swagger-ui",
                deepLinking: true,
                // Champs éditables immédiatement (pas besoin de « Try it out »)
                tryItOutEnabled: true,
                presets: presets,
                plugins: [SwaggerUIBundle.plugins.DownloadUrl],
                layout: presetAvailable ? "StandaloneLayout" : "BaseLayout"
            });
        }

        // ------------------------------------------------------------------
        // Préremplissage automatique de appId à partir de la clé API
        // (une clé d'application n'est liée qu'à une seule app)
        // ------------------------------------------------------------------
        function getAuthorizedApiKey() {
            try {
                var authorized = window.ui.getSystem().authSelectors.authorized();
                if (!authorized || typeof authorized.get !== "function") {
                    return null;
                }
                var entry = authorized.get("ApiKeyAuth");
                if (!entry || typeof entry.get !== "function") {
                    return null;
                }
                return entry.get("value") || null;
            } catch (e) {
                return null;
            }
        }

        // Vrai pendant qu'on écrit nous-mêmes dans un champ (évite de prendre
        // nos propres événements pour une saisie manuelle).
        var autoFilling = false;

        function setReactInputValue(input, value) {
            var descriptor = Object.getOwnPropertyDescriptor(
                window.HTMLInputElement.prototype,
                "value"
            );
            autoFilling = true;
            try {
                descriptor.set.call(input, value);
                input.dispatchEvent(new Event("input", { bubbles: true }));
                input.dispatchEvent(new Event("change", { bubbles: true }));
            } finally {
                autoFilling = false;
            }
        }

        function startAppIdAutofill() {
            var lastKey = null;
            var currentAppId = null;
            var currentAcl = null;
            var resolvedKey = null;
            var pending = false;
            // Champs appId modifiés à la main : on ne les écrase plus
            var userEditedInputs = new WeakSet();

            function updateHint(appId, acl) {
                var hint = document.getElementById("appid-autofill-hint");
                if (!hint) {
                    hint = document.createElement("div");
                    hint.id = "appid-autofill-hint";
                    var swagger = document.getElementById("swagger-ui");
                    swagger.parentNode.insertBefore(hint, swagger);
                }

                if (appId) {
                    hint.textContent =
                        "Clé API liée à l'application « " +
                        appId +
                        " »" +
                        (acl !== null && acl !== undefined ? " (ACL " + acl + ")" : "") +
                        " — les champs appId sont préremplis automatiquement.";
                    hint.style.display = "block";
                } else {
                    hint.textContent = "";
                    hint.style.display = "none";
                }
            }

            function fillAppIdInputs(force) {
                if (!currentAppId) {
                    return;
                }
                var inputs = document.querySelectorAll('input[placeholder="appId"]');
                Array.prototype.forEach.call(inputs, function (input) {
                    if (userEditedInputs.has(input)) {
                        return;
                    }
                    if (!force && input.value) {
                        return;
                    }
                    if (input.value === currentAppId) {
                        return;
                    }
                    setReactInputValue(input, currentAppId);
                });
            }

            // Les exemples cURL de la doc sont statiques ("mon-app" / "1.2.3") :
            // on les met à jour avec l'appId détecté et la version saisie,
            // puis on les colore et on ajoute un bouton « Copier ».
            var curlOriginals = new WeakMap();

            function getScopeInputValue(scope, placeholder) {
                var container = scope && scope.closest ? scope.closest(".opblock") : null;
                var input = (container || document).querySelector(
                    'input[placeholder="' + placeholder + '"]'
                );
                if (!input || !input.value) {
                    return null;
                }
                return input.value;
            }

            function escapeHtml(text) {
                return String(text)
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;");
            }

            function highlightCurl(text) {
                var pattern = /('https?:[^']*')|("(?:[^"\\]|\\.)*")|(\$[A-Z_][A-Z0-9_]*)|\b(curl)\b|(-X\s+(?:GET|POST|PUT|PATCH|DELETE))|(-[HFd])\b/g;
                var html = "";
                var index = 0;
                var match;

                while ((match = pattern.exec(text)) !== null) {
                    html += escapeHtml(text.slice(index, match.index));

                    var cls = "curl-cmd";
                    if (match[1]) {
                        cls = "curl-url";
                    } else if (match[2]) {
                        cls = "curl-string";
                    } else if (match[3]) {
                        cls = "curl-var";
                    } else if (match[5]) {
                        cls = "curl-method";
                    } else if (match[6]) {
                        cls = "curl-flag";
                    }

                    html +=
                        '<span class="' + cls + '">' + escapeHtml(match[0]) + "</span>";
                    index = match.index + match[0].length;
                }

                return html + escapeHtml(text.slice(index));
            }

            function legacyCopyToClipboard(text) {
                var textarea = document.createElement("textarea");
                textarea.value = text;
                textarea.style.position = "fixed";
                textarea.style.opacity = "0";
                document.body.appendChild(textarea);
                textarea.select();
                try {
                    document.execCommand("copy");
                    return Promise.resolve();
                } catch (error) {
                    return Promise.reject(error);
                } finally {
                    document.body.removeChild(textarea);
                }
            }

            function copyToClipboard(text) {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    // Repli si l'API Clipboard est refusée (permissions, http…)
                    return navigator.clipboard
                        .writeText(text)
                        .catch(function () {
                            return legacyCopyToClipboard(text);
                        });
                }

                return legacyCopyToClipboard(text);
            }

            function addCopyButton(pre, code) {
                pre.classList.add("curl-example");

                if (pre.querySelector(".curl-copy-btn")) {
                    return;
                }

                var button = document.createElement("button");
                button.type = "button";
                button.className = "curl-copy-btn";
                button.textContent = "Copier";
                button.addEventListener("click", function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    copyToClipboard(code.textContent || "")
                        .then(function () {
                            button.textContent = "Copié !";
                            button.classList.add("is-copied");
                            window.setTimeout(function () {
                                button.textContent = "Copier";
                                button.classList.remove("is-copied");
                            }, 1500);
                        })
                        .catch(function () {
                            button.textContent = "Échec";
                            window.setTimeout(function () {
                                button.textContent = "Copier";
                            }, 1500);
                        });
                });

                pre.appendChild(button);
            }

            function updateCurlExamples() {
                Array.prototype.forEach.call(
                    document.querySelectorAll("pre"),
                    function (pre) {
                        var code = pre.querySelector("code") || pre;
                        var original = curlOriginals.get(code);

                        if (original === undefined) {
                            var initial = code.textContent || "";
                            if (initial.indexOf("curl") === -1) {
                                return;
                            }
                            original = initial;
                            curlOriginals.set(code, original);
                        }

                        var appId = getScopeInputValue(pre, "appId") || currentAppId;
                        var version = getScopeInputValue(pre, "version");
                        var replacements = [
                            ["mon-app", appId],
                            ["1.2.3", version],
                        ];

                        var text = original;
                        replacements.forEach(function (pair) {
                            if (pair[1]) {
                                text = text.split(pair[0]).join(pair[1]);
                            }
                        });

                        var html = highlightCurl(text);
                        if (code.innerHTML !== html) {
                            code.innerHTML = html;
                        }

                        addCopyButton(pre, code);
                    }
                );
            }

            function resolveAppId(key) {
                if (!key) {
                    currentAppId = null;
                    currentAcl = null;
                    resolvedKey = null;
                    userEditedInputs = new WeakSet();
                    updateHint(null);
                    updateCurlExamples();
                    return;
                }
                if (pending || key === resolvedKey) {
                    return;
                }
                pending = true;

                fetch("/api/whoami", { headers: { "X-API-Key": key } })
                    .then(function (response) {
                        return response.ok ? response.json() : null;
                    })
                    .then(function (identity) {
                        resolvedKey = key;
                        currentAppId =
                            identity && identity.appId ? identity.appId : null;
                        currentAcl =
                            identity && identity.acl !== undefined
                                ? identity.acl
                                : null;
                        // Nouvelle clé : on repart des valeurs de l'application
                        userEditedInputs = new WeakSet();
                        updateHint(currentAppId, currentAcl);
                        if (currentAppId) {
                            fillAppIdInputs(true);
                        }
                        updateCurlExamples();
                    })
                    .catch(function () {
                        currentAppId = null;
                        currentAcl = null;
                        updateHint(null);
                    })
                    .then(function () {
                        pending = false;
                    });
            }

            // Les champs ne sont éditables qu'après « Try it out » : on refait
            // un passage juste après chaque clic.
            document.addEventListener("click", function () {
                window.setTimeout(function () {
                    fillAppIdInputs(false);
                    updateCurlExamples();
                }, 50);
            });

            // Met à jour l'exemple quand la version saisie change et mémorise
            // les appId saisis à la main pour ne pas les écraser.
            document.addEventListener("input", function (event) {
                if (autoFilling) {
                    return;
                }

                var target = event.target;
                if (!target || !target.getAttribute) {
                    return;
                }

                var placeholder = target.getAttribute("placeholder");

                if (placeholder === "appId") {
                    userEditedInputs.add(target);
                } else if (placeholder === "version") {
                    window.setTimeout(updateCurlExamples, 0);
                }
            });

            // Surveille la clé API (stockée par Swagger UI) et les champs appId
            setInterval(function () {
                var key = getAuthorizedApiKey();
                if (key !== lastKey) {
                    lastKey = key;
                    resolveAppId(key);
                } else {
                    fillAppIdInputs(false);
                    updateCurlExamples();
                }
            }, 300);
        }

        loadBundle(0)
            .then(function () { return loadPreset(0); })
            .then(init)
            .then(startAppIdAutofill)
            .catch(showError);
    })();
    </script>
</body>
<style>
.swagger-ui .markdown p, .swagger-ui .markdown pre, .swagger-ui .renderedMarkdown p, .swagger-ui .renderedMarkdown pre {
    margin-top: 0em !important;
}
</style>
</html>