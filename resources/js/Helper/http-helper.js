export function httpGet(url){
    return fetch(url, {
        headers: {
            'Countent-Type': 'application/json',
            'Accept': 'application/json'
        }
    }).then(response => response.json())

}