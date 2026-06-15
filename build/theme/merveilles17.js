"use strict";
// for bottom script only

/* Split ? */
if (typeof Split !== "undefined") {
  var splitH = Split(["#aside", "#main"], { sizes: [30, 70], gutterSize: 3 });
  var aside = document.getElementById("aside");
  var main = document.getElementById("main");
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

function hitoks(form, style) {
  let count = 0;
  let matches = document.querySelectorAll("a." + form);
}

// biblio, hilite doctype
const hash = window.location.hash.substring(1);
let cont = document.getElementById("docs");
if (cont && hash) docs.className = hash;
var matches = document.querySelectorAll("header.doctype > div");
for (var i = 0; i < matches.length; ++i) {
  matches[i].addEventListener(
    "click",
    function (e) {
      if (this.parentNode.parentNode.className == this.className) {
        this.parentNode.parentNode.className = "";
        window.location.hash = "";
      } else {
        this.parentNode.parentNode.className = this.className;
        window.location.hash = "#" + this.className;
      }
    },
    false,
  );
}

// chrono, set links to active
var matches = document.querySelectorAll("a.caldate");
for (var i = 0; i < matches.length; ++i) {
  matches[i].addEventListener(
    "click",
    function (e) {
      var as = document.querySelectorAll("a.caldate");
      for (let j = 0; j < as.length; j++) as[j].classList.remove("active");
      this.classList.add("active");
    },
    false,
  );
}

/** Role, group and gender as filters in the table */
var rolist = document.querySelector(".rolist");
var techlist = document.querySelector(".techlist");
var role_matches = document.querySelectorAll(
  "nav.filters a.role, nav.roles a.role",
);
var group_matches = document.querySelectorAll("nav.filters a.group");
var gender_matches = document.querySelectorAll("nav.filters a.gender");
var tech_matches = document.querySelectorAll("nav.filters a.technique");
if (rolist) {
  // active filters per group: union within group, intersection across groups
  const active = {
    roles: new Set(),
    groups: new Set(),
    genders: new Set(),
  };

  function applyFilters() {
    const rows = rolist.querySelectorAll("tbody tr");
    for (let i = 0; i < rows.length; i++) {
      const row = rows[i];

      // check roles
      let okRole = active.roles.size == 0;
      if (!okRole) {
        for (const r of active.roles)
          if (row.classList.contains(r)) {
            okRole = true;
            break;
          }
      }
      // check groups
      let okGroup = active.groups.size == 0;
      if (!okGroup) {
        for (const g of active.groups)
          if (row.classList.contains(g)) {
            okGroup = true;
            break;
          }
      }
      // check genders
      let okGender = active.genders.size == 0;
      if (!okGender) {
        for (const s of active.genders)
          if (row.classList.contains(s)) {
            okGender = true;
            break;
          }
      }

      const show = okRole && okGroup && okGender;
      row.style.display = show ? "" : "none";
    }
  }

  function toggleFilter(set, token) {
    if (set.has(token)) set.delete(token);
    else set.add(token);
    applyFilters();
  }

  for (var i = 0; i < role_matches.length; ++i) {
    role_matches[i].addEventListener(
      "click",
      function (e) {
        e.preventDefault();
        var role = this.href.split("#")[1];
        this.classList.toggle("active");
        rolist.classList.toggle(role);
        toggleFilter(active.roles, role);
      },
      false,
    );
  }
  for (var i = 0; i < group_matches.length; ++i) {
    group_matches[i].addEventListener(
      "click",
      function (e) {
        e.preventDefault();
        var groupe = this.href.split("#")[1];
        this.classList.toggle("active");
        rolist.classList.toggle(groupe);
        toggleFilter(active.groups, groupe);
      },
      false,
    );
  }
  for (var i = 0; i < gender_matches.length; ++i) {
    gender_matches[i].addEventListener(
      "click",
      function (e) {
        e.preventDefault();
        var gender = this.href.split("#")[1];
        this.classList.toggle("active");
        rolist.classList.toggle(gender);
        toggleFilter(active.genders, gender);
      },
      false,
    );
  }
}

if (techlist) {
  for (var i = 0; i < tech_matches.length; ++i) {
    tech_matches[i].addEventListener(
      "click",
      function (e) {
        e.preventDefault();
        var technique = this.href.split("#")[1];
        this.classList.toggle("active");
        techlist.classList.toggle(technique);
      },
      false,
    );
  }
}

/** Filters for the places */
var placelist = document.querySelector(".placelist");
var placegroup = document.querySelector("nav.filters div.placegroup");
var place_matches = document.querySelectorAll("nav.filters a.place");
var place_notice_matches = document.querySelectorAll("nav.filters a.notice");
var doc_matches = document.querySelectorAll("a.document");
var h3_doc_matches = document.querySelectorAll(".event div");
let hasActivePlace = false;
let listActivePlaces = [];

function actuActivePlaces(place_id) {
  place_matches.forEach((match) => {
    const matchId = parseInt(match.href.split("#")[1]);
    if (listActivePlaces.includes(matchId)) {
      match.classList.add("active");
    } else {
      match.classList.remove("active");
    }
  });
}

function showAllDocuments() {
  doc_matches.forEach((doc) => {
    doc.style.display = "";
  });
}

function getDescendantPlaceIds(place_id) {
  const ids = [place_id];
  place_matches.forEach((match) => {
    const candidateId = parseInt(match.href.split("#")[1]);
    if (candidateId !== place_id && isDescendant(match, place_id)) {
      ids.push(candidateId);
    }
  });
  return ids;
}

function filterDocumentsByPlace(place_id) {
  if (!doc_matches.length) return;
  if (place_id === undefined || place_id === null) {
    showAllDocuments();
    return;
  }

  const descendantIds = getDescendantPlaceIds(place_id).map(String);
  doc_matches.forEach((doc) => {
    const docClasses = Array.from(doc.classList);
    const show = descendantIds.some((id) => docClasses.includes(id));
    doc.style.display = show ? "" : "none";
  });
}

function showChildren(place_id) {
  place_matches.forEach((match) => {
    if (match.href.split("#")[2] == place_id) {
      match.parentNode.style.display = "flex";
    }
  });
}

function hideChildren(place_id) {
  place_matches.forEach((match) => {
    if (match.href.split("#")[2] == place_id) {
      match.parentNode.style.display = "none";
    }
  });
}

function showSiblings(place_parent) {
  place_matches.forEach((match) => {
    if (match.href.split("#")[2] == place_parent) {
      match.parentNode.style.display = "flex";
    }
  });
}

function hideSiblings(place_parent) {
  place_matches.forEach((match) => {
    if (match.href.split("#")[2] == place_parent) {
      match.parentNode.style.display = "none";
    }
  });
}

function isDescendant(match, ancestorId) {
  let parentId = parseInt(match.href.split("#")[2]);
  while (!isNaN(parentId) && parentId !== 0) {
    if (parentId === ancestorId) return true;
    const parentMatch = Array.prototype.find.call(
      place_matches,
      (m) => parseInt(m.href.split("#")[1]) === parentId,
    );
    if (!parentMatch) break;
    parentId = parseInt(parentMatch.href.split("#")[2]);
  }
  return false;
}

function hideDescendants(place_id) {
  place_matches.forEach((match) => {
    if (isDescendant(match, place_id)) {
      match.parentNode.style.display = "none";
      match.parentNode.classList.remove("active");
    }
  });
}

function hideEmptyH3s() {
  const events = document.querySelectorAll("section.event");
  events.forEach((event) => {
    const documents = event.querySelectorAll("a.document");

    // Vérifier si au moins un document est visible
    let hasVisibleDoc = false;
    documents.forEach((doc) => {
      if (doc.style.display !== "none") {
        hasVisibleDoc = true;
      }
    });

    event.style.display = hasVisibleDoc ? "" : "none";
  });
}

if (placelist) {
  place_matches.forEach((match) => {
    match.addEventListener("click", function (e) {
      e.preventDefault();
      const place_id = parseInt(this.href.split("#")[1]);
      const place_parent = parseInt(this.href.split("#")[2]);
      const lastActivePlaceId = listActivePlaces[listActivePlaces.length - 1];

      if (
        lastActivePlaceId === undefined ||
        place_parent === lastActivePlaceId
      ) {
        // Si aucun lieu n'est actif OU si le lieu cliqué est un enfant du dernier lieu actif
        listActivePlaces.push(place_id);
        this.parentNode.classList.add("active");
        showChildren(place_id);
        hideSiblings(place_parent);
        this.parentNode.style.display = "flex";
      } else if (lastActivePlaceId === place_id) {
        // Si le lieu cliqué est le même que le dernier lieu actif
        listActivePlaces.pop();
        this.parentNode.classList.remove("active");
        hideDescendants(place_id);
        showSiblings(place_parent);
      } else if (listActivePlaces.includes(place_id)) {
        // Si le lieu cliqué est un ancêtre du dernier lieu actif
        while (listActivePlaces[listActivePlaces.length - 1] !== place_id) {
          const removedId = listActivePlaces.pop();
          place_matches.forEach((match) => {
            if (parseInt(match.href.split("#")[1]) === removedId) {
              match.parentNode.classList.remove("active");
            }
          });
          hideDescendants(removedId);
        }
        hideDescendants(place_id);
        // Ajouter la classe "active" au lieu cliqué et afficher ses enfants
        this.parentNode.classList.add("active");
        showChildren(place_id);
        hideSiblings(place_parent);
        this.parentNode.style.display = "flex";
      }
      actuActivePlaces(place_id);
      const currentPlaceId = listActivePlaces[listActivePlaces.length - 1];
      filterDocumentsByPlace(currentPlaceId);
      hideEmptyH3s();
    });
  });
}

// var last_place = null;

// function actuPlace(id, parent, hasActivePlace) {
//   // Boucle sur chaque lieu
//   place_matches.forEach((match) => {
//     // Si le lieu cliqué contient la classe .active
//     if (hasActivePlace == true) {
//       // Tester si c'est le lieu cliqué
//       if (match.href.split("#")[1] == id) {
//         match.style.display = "block";
//       } else {
//         // Tester s'il est sibling
//         if (match.href.split("#")[2] == parent) {
//           match.style.display = "none";
//         }
//         // Tester s'il est enfant
//         if (match.href.split("#")[2] == id) {
//           match.style.display = "block";
//         }
//       }
//     }
//     // Si le lieu cliqué ne contient pas la classe .active
//     if (hasActivePlace == false) {
//       // Tester si c'est le lieu cliqué
//       if (match.href.split("#")[1] == id) {
//         match.style.display = "block";
//       } else {
//         // Tester s'il est sibling
//         if (match.href.split("#")[2] == parent) {
//           match.style.display = "block";
//         }
//         // Tester s'il est enfant
//         if (match.href.split("#")[2] == id) {
//           match.style.display = "none";
//           match.classList.remove("active");
//         }
//       }
//     }
//   });
// }

// if (placelist) {
//   place_matches.forEach((match) => {
//     match.addEventListener("click", function (e) {
//       e.preventDefault();
//       var place_id = this.href.split("#")[1];
//       var place_parent = this.href.split("#")[2];
//       // placelist.classList.toggle(place_id);
//       // console.log(place_id, place_parent);
//       placelist.classList.remove(last_place);
//       console.log("Dernier lieu en mémoire : ", last_place);
//       this.classList.toggle("active");
//       if (this.classList.contains("active")) {
//         // Si le lieu n'avait pas encore la classe .active
//         hasActivePlace = true;
//         listActivePlaces.push(place_id);
//       } else {
//         // Si le lieu avait déjà la classe .active
//         hasActivePlace = false;
//         listActivePlaces.splice(listActivePlaces.indexOf(place_id));
//       }
//       // placelist.classList.remove(listActivePlaces.length - 1);
//       placelist.classList.add(place_id);
//       console.log(listActivePlaces);
//       console.log(placelist.classList);
//       last_place = place_id;
//       actuPlace(place_id, place_parent, hasActivePlace);
//     });
//   });
// }

class Merveilles17 {
  static init() {
    Merveilles17.initViewer();
    Merveilles17.initFacs();
    Merveilles17.explorable = document.getElementById("explorable");
    Merveilles17.scroller = Merveilles17.getScrollMother(
      Merveilles17.explorable,
    );
    if (!Merveilles17.explorable) return;
    Merveilles17.initExplorable();
    Merveilles17.explorer = document.getElementById("explorer");
    if (Merveilles17.explorer) Merveilles17.initExplorer();
  }

  static initFacs() {
    // instancier le viewer sur des images
    let els = document.querySelectorAll("a.facs");
    for (let i = 0, max = els.length; i < max; i++) {
      let bigger = "bigger";
      let el = els[i];
      el.addEventListener(
        "click",
        function (e) {
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
        },
        false,
      );
    }
  }

  static initViewer() {
    if (typeof Viewer === "undefined") return;
    // instancier le viewer sur des images
    let els = document.querySelectorAll(".iiif");
    for (let i = 0, max = els.length; i < max; i++) {
      let a = els[i];
      a.addEventListener(
        "click",
        function (e) {
          e.preventDefault();
        },
        false,
      );
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

  static initExplorable(id) {
    let classes = ["persName", "tech", "name", "placeName", "ana"];
    for (const cls of classes) {
      let matches = Merveilles17.explorable.querySelectorAll("." + cls);
      for (let i = 0, max = matches.length; i < max; i++) {
        let el = matches[i];
        el.addEventListener("click", function () {
          let key = this.getAttribute("data-key");
          if (!key) key = cls + "nokey";
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
            if (parent.tagName.toLowerCase() != "details") {
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

  static initExplorer(id) {
    let els = Merveilles17.explorer.getElementsByTagName("details");
    for (let i = 0, max = els.length; i < max; i++) {
      let el = els[i];
      el.addEventListener(
        "toggle",
        function (evt) {
          if (el.open) {
            Merveilles17.explorable.classList.add(el.id);
          } else {
            Merveilles17.explorable.classList.remove(el.id);
          }
        },
        false,
      );
    }

    els = Merveilles17.explorer.getElementsByTagName("a");
    let bookmarks = document.getElementById("bookmarks");
    let mark2clone;
    if (bookmarks) {
      // hack to avoid create element from browser xslt
      mark2clone = bookmarks.querySelector(".toclone");
    }
    for (let i = 0, max = els.length; i < max; i++) {
      let el = els[i];
      if (!el.id) continue; // sommaire ?
      el.addEventListener("click", function (event) {
        let height = Merveilles17.explorable.scrollHeight; // known only when document loaded
        let tag = this.getAttribute("data-tag");
        let terms = Merveilles17.explorable.querySelectorAll("." + el.id);
        if (el.classList.contains("active")) {
          for (let z = 0, max = terms.length; z < max; z++) {
            terms[z].classList.remove("active");
          }
          if (bookmarks) {
            let marks = bookmarks.querySelectorAll("mark." + el.id);
            for (let z = 0, max = marks.length; z < max; z++) {
              marks[z].remove();
            }
          }
          el.classList.remove("active");
        } else {
          el.classList.add("active");
          for (let z = 0, max = terms.length; z < max; z++) {
            terms[z].classList.add("active");
            if (mark2clone) {
              let mark = mark2clone.cloneNode(true); // hack for xsl transform in browser
              mark.className = tag + " " + el.id;
              let top = Merveilles17.top(terms[z]);
              mark.setAttribute("data-offsetTop", top);
              mark.addEventListener("click", Merveilles17.mark);
              mark.style.top = Math.round((1000 * top) / height) / 10 + "%";
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

  static isInView(elem) {
    var bounding = elem.getBoundingClientRect();
    return (
      bounding.top >= 0 &&
      bounding.left >= 0 &&
      bounding.bottom <=
        (window.innerHeight || document.documentElement.clientHeight) &&
      bounding.right <=
        (window.innerWidth || document.documentElement.clientWidth)
    );
  }

  static mark(e) {
    let scroll = this.getAttribute("data-offsetTop") - 20;
    Merveilles17.scroller.scrollTo(0, scroll);
  }

  static top(node) {
    var top = 0;
    do {
      top += node.offsetTop;
      node = node.offsetParent;
    } while (node && node.tagName.toLowerCase() != "body");
    return top;
  }

  static getScrollMother(node) {
    if (node == null) return null;
    if (node == document) return window;

    let overflowY = window.getComputedStyle(node).overflowY;
    let scrollable = overflowY !== "visible" && overflowY !== "hidden";
    if (scrollable) return node;
    return Merveilles17.getScrollMother(node.parentNode);
  }
}

Merveilles17.init();
