const Ajax = (set) => {

    if(typeof set === 'undefined') set = {};

    // 1. если не определен url
    if(typeof set.url === 'undefined' || !set.url) {
        // 2. Если есть path, то положим его
        set.url = typeof PATH !== 'undefined' ? PATH : '/';
    }

    // 3. Если не определен тип запроса (POST/GET)
    if(typeof set.type === 'undefined' || !set.type) set.type = 'GET';

    set.type = set.type.toUpperCase();

    let body = '';

    if(typeof set.data !== 'undefined' && set.data) {
        for(let i in set.data) {
            if(set.data.hasOwnProperty(i)) {
                body += '&' + i + '=' + set.data[i];
            }
        }

        body = body.substr(1);

    }

    if(typeof ADMIN_MODE !== 'undefined') {
        body += body ? '&' : '';
        body += 'ADMIN_MODE=' + ADMIN_MODE;
    }

    if(set.type === 'GET') {
        set.url += '?' + body;
        body = null;
    }

    return new Promise((resolve, reject) => {

        let xhr = new XMLHttpRequest();

        xhr.open(set.type, set.url, true);

        let contentType = false;

        if(typeof set.headers !== 'undefined' && set.headers) {

            for(let i in set.headers) {
                xhr.setRequestHeader(i, set.headers['i']);

                if(i.toUpperCase() === 'content-type') contentType = true;

            }
        }

        if(!contentType) xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');

        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.onload = function() {
            if(this.status >= 200 && this.status < 300) {
                if(/fatal\s+?error/ui.test(this.response)) {
                    reject(this.response);
                }
                resolve(this.response);
            }

            reject(this.response);
        }


        xhr.onerror = function() {
            reject(this.response);
        }

        xhr.send(body);

    });

};


// $.ajax({
//    url:'/',
//    type: 'POST',
//    data: {
//        ajax:'blabla'
//    },
//    success: function(){
//    },
//    error: function() {
//    }
// });