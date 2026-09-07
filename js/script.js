function getImg(data) {
    const imgContainer = document.getElementById("gallery");
    imgContainer.innerHTML = "";

    if (data.items.length > 0) {
        const firstItem = data.items[Math.floor(Math.random() * data.items.length)];
        const img = document.createElement("img");
        img.src = firstItem.media.m;
        img.alt = firstItem.title;
        imgContainer.appendChild(img);
  }

}


function displayImg() {
    const script = document.createElement("script");
    script.src = "https://www.flickr.com/services/feeds/photos_public.gne?format=json&jsoncallback=getImg&tags=cats";
    document.body.appendChild(script);

}
    
displayImg();