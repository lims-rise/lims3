{{-- Dashboard button: downloads every CSV/Excel export available to this user as one ZIP. --}}
<div class="card border-0 shadow-sm mb-3" id="exportall-card">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h6 class="mb-0">Download all data</h6>
                <small class="text-muted">
                    Every CSV and Excel export you have access to, for the selected lab, in one ZIP file.
                </small>
            </div>
            <button type="button" id="exportall-btn" class="btn btn-success btn-sm">
                <i class="fa fa-download me-1"></i> Download all exports (ZIP)
            </button>
        </div>

        <div id="exportall-panel" class="mt-3 d-none">
            <div class="progress mb-2" style="height: 18px">
                <div id="exportall-bar" class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%">0%</div>
            </div>
            <div class="small" id="exportall-status">Preparing...</div>
            <div class="small text-danger" id="exportall-errors"></div>
            <button type="button" id="exportall-cancel" class="btn btn-outline-secondary btn-sm mt-2">Cancel</button>
        </div>
    </div>
</div>

@push('scripts')
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script> -->
    <script src="{{ asset('js/jszip.min.js') }}"></script>
    <script>
        (function () {
            const manifestUrl = @json(route('dashboard.export-all'));
            const btn      = document.getElementById('exportall-btn');
            const panel    = document.getElementById('exportall-panel');
            const bar      = document.getElementById('exportall-bar');
            const statusEl = document.getElementById('exportall-status');
            const errorsEl = document.getElementById('exportall-errors');
            const cancel   = document.getElementById('exportall-cancel');
            let controller = null;

            function setBar(done, total) {
                const pct = total ? Math.round(done * 100 / total) : 0;
                bar.style.width = pct + '%';
                bar.textContent = pct + '%';
            }

            function fileName(res, fallback) {
                const cd = res.headers.get('Content-Disposition') || '';
                let m = /filename\*=UTF-8''([^;]+)/i.exec(cd);
                let name = m ? decodeURIComponent(m[1]) : null;
                if (!name) { m = /filename="?([^";]+)"?/i.exec(cd); name = m ? m[1] : fallback; }
                return name.replace(/[\\/:*?"<>|]/g, '_');
            }

            cancel.addEventListener('click', function () { if (controller) controller.abort(); });

            btn.addEventListener('click', async function () {
                if (typeof JSZip === 'undefined') {
                    alert('The ZIP library could not be loaded. Check the internet connection and reload the page.');
                    return;
                }
                if (!confirm('Download all exports? This can take several minutes. Keep this page open until the ZIP is ready.')) return;

                btn.disabled = true;
                panel.classList.remove('d-none');
                errorsEl.textContent = '';
                statusEl.textContent = 'Preparing the list of files...';
                setBar(0, 1);
                controller = new AbortController();

                const failed = [];
                let aborted = false;

                try {
                    const mres = await fetch(manifestUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json' }, signal: controller.signal });
                    if (!mres.ok) throw new Error('HTTP ' + mres.status);
                    const manifest = await mres.json();
                    const files = manifest.files;
                    if (!files.length) { statusEl.textContent = 'There is nothing to download for your access level.'; return; }

                    const zip = new JSZip();
                    const summary = [];

                    for (let i = 0; i < files.length; i++) {
                        const f = files[i];
                        statusEl.textContent = (i + 1) + ' / ' + files.length + ' - ' + f.label;
                        setBar(i, files.length);
                        try {
                            const res = await fetch(f.url, { credentials: 'same-origin', signal: controller.signal });
                            const type = res.headers.get('Content-Type') || '';
                            if (!res.ok) throw new Error('HTTP ' + res.status);
                            if (type.indexOf('text/html') === 0) throw new Error('unexpected page (session expired?)');
                            const blob = await res.blob();
                            const name = fileName(res, f.name + '.dat');
                            zip.file(f.folder + '/' + name, blob);
                            summary.push('OK     ' + f.folder + '/' + name);
                        } catch (e) {
                            if (e.name === 'AbortError') { aborted = true; break; }
                            failed.push(f.label + ': ' + e.message);
                            summary.push('FAILED ' + f.label + ' - ' + e.message);
                        }
                    }

                    if (aborted) { statusEl.textContent = 'Cancelled.'; return; }

                    setBar(files.length, files.length);
                    statusEl.textContent = 'Creating the ZIP file...';
                    zip.file('_summary.txt', 'Lab: ' + manifest.lab + '\nCreated: ' + new Date().toISOString() + '\n\n' + summary.join('\n') + '\n');

                    const blob = await zip.generateAsync(
                        { type: 'blob', compression: 'DEFLATE', compressionOptions: { level: 3 } },
                        function (meta) { statusEl.textContent = 'Creating the ZIP file... ' + Math.round(meta.percent) + '%'; }
                    );

                    const a = document.createElement('a');
                    a.href = URL.createObjectURL(blob);

                    const today = new Date();
                    const year = today.getFullYear();
                    const month = String(today.getMonth() + 1).padStart(2, '0');
                    const day = String(today.getDate()).padStart(2, '0');
                    const formattedDate = `${year}${month}${day}`;

                    a.download = 'LIMS3_exports_' + manifest.lab + '_' + formattedDate + '.zip';
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    setTimeout(function () { URL.revokeObjectURL(a.href); }, 60000);

                    statusEl.textContent = 'Done: ' + (files.length - failed.length) + ' of ' + files.length + ' files are in the ZIP.';
                    if (failed.length) errorsEl.textContent = failed.length + ' file(s) could not be created, see _summary.txt in the ZIP.';
                } catch (e) {
                    statusEl.textContent = e.name === 'AbortError' ? 'Cancelled.' : 'Failed: ' + e.message;
                } finally {
                    btn.disabled = false;
                    bar.classList.remove('progress-bar-animated');
                    controller = null;
                }
            });
        })();
    </script>
@endpush
