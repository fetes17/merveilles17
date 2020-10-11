'use strict';
// for bottom script only

// instancier le viewer sur des images
let els = document.querySelectorAll('.iiif');
for (let i = 0, max = els.length; i < max; i++) {
  let a = els[i];
  a.addEventListener("click",function(e){
    e.preventDefault();
  },false);
  new Viewer(a, {
    inline: false,
    navbar: false,
    url(image) {
      return a.href;
    },
    toolbar: {
      zoomIn: 4,
      zoomOut: 4,
      oneToOne: 4,
      reset: 4,
      prev: 0,
      play: {
        show: 0,
        size: 'large',
      },
      next: 0,
      rotateLeft: 0,
      rotateRight: 0,
      flipHorizontal: 0,
      flipVertical: 0,
    },
  });
}

/* TODO meilleur tests */
if (typeof Split !== 'undefined') {
  var splitH = Split(['#aside', '#main'], { sizes: [30, 70], gutterSize: 3, });
  var aside = document.getElementById('aside');
  var main = document.getElementById('main');
}

if (aside) {
  let els = aside.getElementsByTagName('details');
  for (let i = 0, max = els.length; i < max; i++) {
    let el = els[i];
    el.addEventListener("toggle", function(evt){
      if(el.open) {
        main.classList.add(el.id);
      } else {
        main.classList.remove(el.id);
      }
    }, false);
  }
  
  els = aside.getElementsByTagName('a');
  let bookmarks = document.getElementById('bookmarks');
  let mark2clone;
  if (bookmarks) { // hack to avoid create element from browser xslt 
    mark2clone = bookmarks.querySelector(".toclone");
  }
  for (let i = 0, max = els.length; i < max; i++) {
    let el = els[i];
    el.addEventListener("click", function(event){
      let height= main.scrollHeight; // known only when document loaded
      let tag = this.getAttribute("data-tag");
      let terms = main.querySelectorAll('.'+el.id);
      if (el.classList.contains('active')) {
        for (let z = 0, max = terms.length; z < max; z++) {
          terms[z].classList.remove('active');
        }
        if(bookmarks) {
          let marks = bookmarks.querySelectorAll('mark.'+el.id);
          for (let z = 0, max = marks.length; z < max; z++) {
            marks[z].remove();
          }
        }
        el.classList.remove('active');
        event.preventDefault();
        return false;
      }
      else {
        el.classList.add('active');
        for (let z = 0, max = terms.length; z < max; z++) {
          terms[z].classList.add('active');
          if (mark2clone) {
            let mark = mark2clone.cloneNode(true); // hack for xsl transform in browser
            mark.className = tag+" "+el.id;
            let top = Merveilles17.top(terms[z]);
            mark.setAttribute("data-offsetTop", top);
            mark.addEventListener("click", Merveilles17.mark);
            mark.style.top = (Math.round(1000*(top) / height) / 10)+'%';
            bookmarks.appendChild(mark);
          }
        }
      }
    });
  }
}

if(main) {
  let classes = ["persName", "tech", "name", "placeName"];
  for (const cls of classes) {
    let matches = main.querySelectorAll("."+cls);
    for (let i = 0, max = matches.length; i < max; i++) {
      let el = matches[i];
      el.addEventListener("click", function(){
        let key = this.getAttribute("data-key");
        if (!key) key = cls+"nokey";
        let target = document.getElementById(key);
        if (!target) return;
        let newHash = '#'+key;
        // if (location.hash == newHash) return; // we can repeat
        location.hash = newHash;
        target.click();
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
  let newScroll = scrollable.scrollTop - 100;
  scrollable.scrollTop = newScroll;
  scrollable.lastScroll = newScroll;
}
if (window.location.hash) propaghi(window.location.hash);

function hitoks(form, style)
{
  let count = 0;
  let matches = document.querySelectorAll("a."+form);
}

class Merveilles17 {
  static mark(e)
  {
    let scroll = this.getAttribute("data-offsetTop") - 20;
    main.scrollTo(0, scroll);
  }
  static top(node)
  {
    var top = 0;
    do {
      top += node.offsetTop;
      node = node.offsetParent;
    } while(node && node.tagName.toLowerCase() != 'body');
    return top;
  }

}

