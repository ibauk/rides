"use strict"

function fix2(x) {

    let xx = '' + x;
    if (xx.length < 2) {
        return '0' + xx;
    }
    return xx;
}
function fix3(x) {

    let xx = fix2(x);
    if (xx.length < 3) {
        return '0' + xx;
    }
    return xx;
}
function timestampToISO(ts) {

    let mth = ts.getMonth();
    let mm = fix2(mth + 1);
    let dd = fix2(ts.getDate());
    let hh = fix2(ts.getHours());
    let mins = fix2(ts.getMinutes());
    let ss = fix2(ts.getSeconds());
    let tt = fix3(ts.getMilliseconds());
    let res = ts.getFullYear() + '-' + mm + '-' + dd;
    res += ' ';
    res += hh + ':' + mins + ':' + ss + '.' + tt;
    return res;
}
async function fetchDataset(url) {

    console.log('Fetching '+url);
    let response = await fetch(url);
    if (!response.ok) {
        alert('OMG!! '+response.status);
        return;
    }
    let json = await response.text();
    console.log('Fetched ' + json);
    localStorage.clear();
    localStorage.setItem('asat',timestampToISO(new Date()));
    localStorage.setItem('json',json);
}

function showstuff() {

    let div = document.querySelector('#stuff');
    let data = JSON.parse(localStorage.getItem('json'));
    div.innerHTML = data.sname;
}