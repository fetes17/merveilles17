'use strict';
// for bottom script only


/* Split ? */
if (typeof Split !== 'undefined') {
  var splitH = Split(['#aside', '#main'], { sizes: [30, 70], gutterSize: 3, });
  var aside = document.getElementById('aside');
  var main = document.getElementById('main');
}

/*
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
*/

function hitoks(form, style)
{
  let count = 0;
  let matches = document.querySelectorAll("a."+form);
}

// biblio, hilite doctype
const hash = window.location.hash.substring(1);
let cont = document.getElementById('docs');
if (cont && hash) docs.className = hash;
var matches = document.querySelectorAll("header.doctype > div");
for (var i = 0; i < matches.length; ++i) {
  matches[i].addEventListener("click", function(e) {
    if (this.parentNode.parentNode.className == this.className) {
      this.parentNode.parentNode.className = '';
      window.location.hash = '';
    }
    else {
      this.parentNode.parentNode.className = this.className;
      window.location.hash = '#'+this.className;
    }
  }, false);
}

// chrono, set links to active
var matches = document.querySelectorAll("a.caldate");
for (var i = 0; i < matches.length; ++i) {
  matches[i].addEventListener("click", function(e) {
    var as = document.querySelectorAll("a.caldate");
    for (let j = 0; j < as.length; j++) as[j].classList.remove('active');
    this.classList.add('active');
  }, false);
}

/** Role as a filter in the table */
var rolist = document.querySelector('.rolist');
var matches = document.querySelectorAll("nav.roles a.role");
if (rolist) {
  for (var i = 0; i < matches.length; ++i) {
    matches[i].addEventListener("click", function(e) {
      e.preventDefault();
      var role = this.href.split("#")[1];
      if (this.classList.contains("active")) {
        this.classList.remove('active');
        rolist.classList.remove(role);
      }
      else {
        this.classList.add('active');
        rolist.classList.add(role);
      }
    }, false);
  }
}

class Merveilles17 {

  static init()
  {
    Merveilles17.initViewer();
    Merveilles17.initFacs();
    Merveilles17.explorable = document.getElementById('explorable');
    Merveilles17.scroller = Merveilles17.getScrollMother(Merveilles17.explorable);
    if(!Merveilles17.explorable) return;
    Merveilles17.initExplorable();
    Merveilles17.explorer = document.getElementById('explorer');
    if (Merveilles17.explorer) Merveilles17.initExplorer();
  }
  
  static initFacs()
  {
    // instancier le viewer sur des images
    let els = document.querySelectorAll('a.facs');
    for (let i = 0, max = els.length; i < max; i++) {
      let bigger = 'bigger';
      let el = els[i];
      el.addEventListener("click", function(e) {
        e.preventDefault();
        let img = el.querySelector("img");
        if (el.classList.contains(bigger)) {
          el.classList.remove(bigger);
          if (img.oldsrc) {
            img.src = img.oldsrc;
            img.oldsrc = null;
          }
        } else {
          el.classList.add(bigger);
          let url = img.getAttribute("data-bigger");
          if (url) {
            img.oldsrc = img.src;
            img.src = url;
          }
        }
      }, false);
    }
  }
  
  static initViewer()
  {
    if (typeof Viewer === 'undefined') return;
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
          },
          next: 0,
          rotateLeft: 0,
          rotateRight: 0,
          flipHorizontal: 0,
          flipVertical: 0,
        },
      });
    }
  }
  
  static initExplorable(id)
  {
    let classes = ["persName", "tech", "name", "placeName", "ana"];
    for (const cls of classes) {
      let matches = Merveilles17.explorable.querySelectorAll("."+cls);
      for (let i = 0, max = matches.length; i < max; i++) {
        let el = matches[i];
        el.addEventListener("click", function() {
          let key = this.getAttribute("data-key");
          if (!key) key = cls+"nokey";
          let target = document.getElementById(key);
          if (!target) return;
          // no hash
          // let newHash = '#'+key;
          // if (location.hash == newHash) return; // we can repeat
          // location.hash = newHash;
          // if (!Merveilles17.isInView(target)) target.scrollIntoView();
          
          // get parent <details> and open it
          let parent = target.parentNode;
          while (parent != null) {
            if (parent.tagName.toLowerCase() != 'details') {
              parent = parent.parentNode;
              continue;
            }
            if (!parent.open) parent.open = true;
            break;
          }
          // ensure scroll into view
          if (!Merveilles17.isInView(target)) {
            let targetY = target.offsetTop;
            let mother = Merveilles17.getScrollMother(target);
            // if (hereY < toc.clientHeight + toc.scrollTop) return;
            console.log(target.offsetTop);
          }
          /*
          var hereY = here.offsetTop;
          if (hereY < toc.clientHeight + toc.scrollTop) return;
          toc.scrollTop = hereY + 100;
          */
          target.click();
          
        });
      }
    }
  }

  static initExplorer(id)
  {
    let els = Merveilles17.explorer.getElementsByTagName('details');
    for (let i = 0, max = els.length; i < max; i++) {
      let el = els[i];
      el.addEventListener("toggle", function(evt){
        if(el.open) {
          Merveilles17.explorable.classList.add(el.id);
        } else {
          Merveilles17.explorable.classList.remove(el.id);
        }
      }, false);
    }
    
    els = Merveilles17.explorer.getElementsByTagName('a');
    let bookmarks = document.getElementById('bookmarks');
    let mark2clone;
    if (bookmarks) { // hack to avoid create element from browser xslt 
      mark2clone = bookmarks.querySelector(".toclone");
    }
    for (let i = 0, max = els.length; i < max; i++) {
      let el = els[i];
      if (!el.id) continue; // sommaire ?
      el.addEventListener("click", function(event) {
        let height= Merveilles17.explorable.scrollHeight; // known only when document loaded
        let tag = this.getAttribute("data-tag");
        let terms = Merveilles17.explorable.querySelectorAll('.'+el.id);
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
          if (terms.length == 1) {
            if (!Merveilles17.isInView(terms[0])) terms[0].scrollIntoView();
          }
        }
        event.preventDefault();
        return false;
      });
    }
  }
  
  static isInView(elem)
  {
    var bounding = elem.getBoundingClientRect();
    return (
      bounding.top >= 0
      && bounding.left >= 0
      && bounding.bottom <= (window.innerHeight || document.documentElement.clientHeight)
      && bounding.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
  }

  
  static mark(e)
  {
    let scroll = this.getAttribute("data-offsetTop") - 20;
    Merveilles17.scroller.scrollTo(0, scroll);
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
  
  static getScrollMother(node)
  {
    if (node == null) return null;
    if (node == document) return window;
    
    let overflowY = window.getComputedStyle(node).overflowY;
    let scrollable = overflowY !== 'visible' && overflowY !== 'hidden';
    if (scrollable) return node;
    return Merveilles17.getScrollMother(node.parentNode);
  }


}

Merveilles17.init();


