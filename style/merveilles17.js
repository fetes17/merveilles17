var splitH = Split(['#aside', '#main'], { sizes: [30, 70], gutterSize: 3, });
var aside = document.getElementById('aside');
if (aside) {
  var as = aside.getElementsByTagName('a');
  for (var i = 0, max = as.length; i < max; i++) {
    var a = as[i];
    a.addEventListener("click", function(){
      if (document.lastAsideA) document.lastAsideA.classList.remove('active');
      this.classList.add('active');
      document.lastAsideA = this;
    });
  }
}
var main = document.getElementById('main');
if(main) {
  classes = ["persName", "tech", "name"];
  for (const cls of classes) {
    let matches = main.querySelectorAll("."+cls);
    for (var i = 0, max = matches.length; i < max; i++) {
      var el = matches[i];
      el.addEventListener("click", function(){
        let key = this.getAttribute("data-key");
        if (!key) key = cls+"nokey";
        var newHash = '#'+key;
        if (location.hash == newHash) return; // do no repeat
        location.hash = newHash;
      });
    }
  }
}


function getScrollParent(node) {
  if (node == null) return null;
  if (node.scrollTop) return node;
  return getScrollParent(node.parentNode);
}

window.onhashchange = function (e)
{
  let url = new URL(e.newURL);
  let hash = url.hash;
  return propaghi(hash);
}

window.onpopstate = function(event) {
  // before scroll
  // console.log("location: " + document.location + ", state: " + JSON.stringify(event.state));
};

function propaghi(hash)
{
  let id = decodeURIComponent(hash);
  if (id[0] == "#") id = id.substring(1);
  let el = document.getElementById(id);
  if (!el) return;
  const scrollable = getScrollParent(el);
  if (!scrollable) return;
  if (scrollable.lastScroll == scrollable.scrollTop) return;
  var newScroll = scrollable.scrollTop - 100;
  scrollable.scrollTop = newScroll;
  scrollable.lastScroll = newScroll;
}
if (window.location.hash) propaghi(window.location.hash);

function hitoks(form, style)
{
  let count = 0;
  let matches = document.querySelectorAll("a."+form);
}


