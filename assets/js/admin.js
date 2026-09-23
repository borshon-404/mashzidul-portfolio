/* MTB Admin — Vanilla JS — M4.2 CRUD */
(function(){
  var sidebar = document.getElementById('admin-sidebar');
  var burger = document.getElementById('admin-burger');
  var closeBtn = document.getElementById('admin-sidebar-close');

  function openSidebar(){
    if(!sidebar) return;
    sidebar.classList.add('is-open');
    if(burger) burger.setAttribute('aria-expanded','true');
    document.body.style.overflow='hidden';
  }
  function closeSidebar(){
    if(!sidebar) return;
    sidebar.classList.remove('is-open');
    if(burger) burger.setAttribute('aria-expanded','false');
    document.body.style.overflow='';
  }

  if(burger){
    burger.addEventListener('click', function(e){
      e.stopPropagation();
      if(sidebar && sidebar.classList.contains('is-open')) closeSidebar(); else openSidebar();
    });
  }
  if(closeBtn){
    closeBtn.addEventListener('click', closeSidebar);
  }
  document.addEventListener('keydown', function(e){
    if(e.key==='Escape') closeSidebar();
  });
  document.addEventListener('click', function(e){
    if(!sidebar || !burger) return;
    if(window.innerWidth<=1024 && sidebar.classList.contains('is-open')){
      if(!sidebar.contains(e.target) && !burger.contains(e.target)){
        closeSidebar();
      }
    }
  });

  // Delete confirmation for forms with data-confirm
  document.addEventListener('submit', function(e){
    var form = e.target;
    if(form && form.getAttribute('data-confirm')){
      var msg = form.getAttribute('data-confirm');
      if(!confirm(msg)){
        e.preventDefault();
      }
    }
  });

  // Dynamic list: add/remove rows for technologies and features
  function initDynamicList(containerId, inputName){
    var container = document.getElementById(containerId);
    if(!container) return;
    var list = container.querySelector('.dynamic-list__items');
    var addBtn = container.querySelector('.dynamic-list__add');
    if(!list || !addBtn) return;

    addBtn.addEventListener('click', function(){
      var item = document.createElement('div');
      item.className = 'dynamic-list__item';
      var input = document.createElement('input');
      input.type = 'text';
      input.name = inputName;
      input.placeholder = inputName.includes('technolog') ? 'e.g. React' : 'e.g. Responsive design';
      input.className = '';
      var removeBtn = document.createElement('button');
      removeBtn.type = 'button';
      removeBtn.className = 'btn btn--ghost btn--sm';
      removeBtn.textContent = 'Remove';
      removeBtn.addEventListener('click', function(){ item.remove(); });
      item.appendChild(input);
      item.appendChild(removeBtn);
      list.appendChild(item);
      input.focus();
    });

    // Bind existing remove buttons
    list.querySelectorAll('.dynamic-list__item .btn--remove').forEach(function(btn){
      btn.addEventListener('click', function(){
        var item = btn.closest('.dynamic-list__item');
        if(item) item.remove();
      });
    });
  }

  initDynamicList('tech-list', 'technologies[]');
  initDynamicList('feat-list', 'features[]');

  // Auto-slug from title
  var titleInput = document.getElementById('field-title');
  var slugInput = document.getElementById('field-slug');
  if(titleInput && slugInput){
    var slugManuallyEdited = false;
    if(slugInput.value) slugManuallyEdited = true;
    slugInput.addEventListener('input', function(){ slugManuallyEdited = true; });
    titleInput.addEventListener('input', function(){
      if(slugManuallyEdited) return;
      var val = titleInput.value.toLowerCase();
      val = val.replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').substring(0, 80);
      slugInput.value = val;
    });
  }
})();
