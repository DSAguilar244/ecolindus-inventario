// Global AJAX + Toast helpers used across the app
window.showGlobalToast = function(message, options = {}){
    // options: { classname, delay }
    const cls = options.classname || 'bg-info text-white';
    const delay = options.delay || 2500;
    let container = document.getElementById('globalToastContainer');
    if(!container){
        container = document.createElement('div');
        container.id = 'globalToastContainer';
        container.style.position = 'fixed';
        container.style.top = '20px';
        container.style.right = '20px';
        container.style.zIndex = 1080;
        document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    toast.className = `alert ${cls} shadow-sm`; 
    toast.role = 'alert';
    toast.innerHTML = message;
    container.appendChild(toast);
    setTimeout(()=>{
        try{ container.removeChild(toast); }catch(e){}
    }, delay);
};

window.ajaxPostForm = async function(formElement, opts = {}){
    // opts: { onSuccess(responseJson), onError(error), reloadOnSuccess }
    const url = formElement.action;
    const tokenEl = formElement.querySelector('input[name="_token"]');
    const token = tokenEl ? tokenEl.value : (document.querySelector('meta[name="csrf-token"]')?.content || '');
    const fm = new FormData(formElement);
    try{
        const resp = await fetch(url, {
            method: formElement.method || 'POST',
            credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
            body: fm
        });
        const json = await resp.json().catch(()=>null);
        if(!resp.ok){
            if(opts.onError) opts.onError(json || { message: resp.statusText });
            return { ok: false, resp, json };
        }
        if(opts.onSuccess) opts.onSuccess(json);
        if(opts.reloadOnSuccess) location.reload();
        return { ok: true, resp, json };
    }catch(err){
        if(opts.onError) opts.onError({ message: err.message });
        return { ok: false, error: err };
    }
};
