window.edit_state = 0;
window.pre_edit_text = "";

function getCookie(cname) {
  let name = cname + "=";
  let decodedCookie = decodeURIComponent(document.cookie);
  let ca = decodedCookie.split(';');
  for(let i = 0; i <ca.length; i++) {
    let c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}
function setCookie(cname, cvalue, exdays) {
    const d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    let expires = "expires="+ d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}
function cookieAccept() {
    setCookie("cookie-consent", "true", 30);
    document.getElementById("cookie-banner").style.visibility = "hidden";
    document.getElementById("content").style.filter = "blur(0)";
}
if (getCookie("cookie-consent") == "") {
    document.getElementById("cookie-banner").style.visibility = "visible";
    document.getElementById("content").style.filter = "blur(10px)";
}

window.token = getCookie("loginSession");

function addQuote(activate) {
  if (activate == 0) {
    document.getElementById("add-quote-div").style.display = "block";
    document.getElementById("add-quote-div").innerHTML = `<form id="add-quote-form" action="https://quotes.philippschuetz.de/api/quotes/">
                                                            <input type="hidden" name="ref" value="${window.location.href}">
                                                            <input type="hidden" name="auth" value="${window.token}">
                                                            <input type="hidden" name="op" value="c">
                                                            <textarea id="textarea-add" class="centered" name="content" rows="4" cols="64" maxlength="1024"></textarea><br>
                                                            <button onclick="addQuote(1);" type="submit" class="centered">Add</button>
                                                            <button onclick="addQuote(1);" type="button">Cancel</button>
                                                          </form>`
  } else if (activate == 1) {
    document.getElementById("add-quote-div").style.display = "none";
    document.getElementById("textarea-add").innerhtml = "";
  }
}

function cancelEdit() {
  if (window.edit_state > 0) {
    quoteid_str = window.edit_state.toString();
    quote_text_div = document.getElementById("quote-text" + quoteid_str);
    quote_text_div.innerHTML = pre_edit_text;
    window.edit_state = 0;
  }
}

function editQuote(quoteid) {
  console.log(window.edit_state);
    if (window.edit_state > 0) {
      alert("You can only edit one quote at a time!");
    }
    else {
      window.edit_state = quoteid;
      quoteid_str = quoteid.toString();
      quote_text_div = document.getElementById("quote-text" + quoteid_str);
      pre_edit_text = quote_text_div.innerHTML;
      window.pre_edit_text = pre_edit_text;
      quote_text_div.innerHTML = `<form id="quote-edit-form" action="https://quotes.philippschuetz.de/api/quotes/">
                                  <input type="hidden" name="ref" value="${window.location.href}">
                                  <input type="hidden" name="auth" value="${window.token}">
                                  <input type="hidden" name="op" value="u">
                                  <input type="hidden" name="quoteid" value="${quoteid}">
                                  <textarea class="textarea-edit" id="textarea-edit${quoteid}" name="content" rows="4" cols="60" maxlength="1024">${pre_edit_text}</textarea><br>
                                  <button type="submit">Edit</button>
                                </form>
                                <button onclick="cancelEdit();">Cancel</button>`
      }
}

// js content lazy loading

// quote search
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.has("search") == true) {
  window.search = urlParams.get("search");
  if (window.search == "") {
    window.search = null;
  }
} else {
  window.search = null;
}

document.addEventListener("DOMContentLoaded", () => {
  //set up IntersectionObserver
  let options = {
    root: null,
    rootMargins: "0px",
    threshold: 0.5
  };
  const observer = new IntersectionObserver(handleIntersect, options);
  observer.observe(document.querySelector("#footer"));
  //initial load of data
  getData(-1, window.token, window.search);
});
function handleIntersect(entries) {
  if (entries[0].isIntersecting) {
    getData(0, window.token, window.search);
  }
}
async function getData(quoteid, token, search) {

  if (quoteid == -1) {
      // request for highest quoteid
      let url = `https://quotes.philippschuetz.de/api/quotes/?auth=${token}&op=mqid`;
      let response = await fetch(url)
      let data = await response.json();
      quoteid = data.data;
  } else if (quoteid != -1 && search == null) {
      //get quoteid of latest quote
      quoteid = parseInt(Array.from(document.getElementsByClassName('quote-text')).pop().id.replace("quote-text", ""));
      quoteid -= 1;
  }

  if (search != null) {
    url = `https://quotes.philippschuetz.de/api/quotes/?auth=${token}&op=r&quoteid=${quoteid}&count=32&search=${search}`;
  } else {
    url = `https://quotes.philippschuetz.de/api/quotes/?auth=${token}&op=r&quoteid=${quoteid}&count=32`;
  }
  let main = document.querySelector("#main");

  if (quoteid != 0) {
    fetch(url)
    .then(response => response.json())
    .then(data => {
        // data.data[].quoteid/username/unix_timestamp/content
        data.data.forEach(data => {
            let quote = document.createElement("div");
            quote.className = "quote";
            let quote_head = document.createElement("div");
            quote_head.className = "quote-head";
            let date_added = document.createElement("div");
            date_added.className = "date-added";
            timestamp_mil = data.unix_timestamp * 1000;
            date_object = new Date(timestamp_mil);
            date = date_object.toLocaleString("de-DE", {day: "numeric", month: "numeric", year: "numeric"});
            date_added.innerHTML = date;
            let author = document.createElement("div");
            author.className = "author";
            author.innerHTML = data.username;
            let edit_button_container = document.createElement("div");
            edit_button_container.className = "edit-button";
            let edit_button = document.createElement("button");
            edit_button.setAttribute('onclick','editQuote(' + data.quoteid + ')');
            edit_button.innerHTML = "Edit";
            let quote_text = document.createElement("div");
            quote_text.className = "quote-text";
            quote_text.id = "quote-text" + data.quoteid;
            quote_text.innerHTML = data.content;
  
            quote_head.appendChild(date_added);
            quote_head.appendChild(author);
            edit_button_container.appendChild(edit_button);
            quote_head.appendChild(edit_button_container);
            quote.appendChild(quote_head);
            quote.appendChild(quote_text);
  
            main.appendChild(quote);
      })
    })
  }
}
