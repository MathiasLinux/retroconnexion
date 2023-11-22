function generateKey(length = 32) {
    // The future key
    let key = "";
    // The possible characters
    let possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    // The length of the possible characters
    let possibleLength = possible.length;
    // The length of the key
    const randomValues = new Uint32Array(length);
    window.crypto.getRandomValues(randomValues);
    randomValues.forEach((value) => {
        key += possible.charAt(value % possibleLength);
    });

    return key;
}

document.querySelector("#generateKey").addEventListener("click", function () {
    let key = document.querySelector("#key");
    key.value = generateKey();
});