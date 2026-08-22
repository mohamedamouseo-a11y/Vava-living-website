(function () {
  'use strict';
  document.addEventListener('DOMContentLoaded', function () {
    var categoryEditor = document.querySelector('.vava-category-i18n');
    if (categoryEditor) {
      var categoryButtons = categoryEditor.querySelectorAll('[data-vava-category-lang]');
      var categoryFields = categoryEditor.querySelector('[data-vava-category-fields]');
      var categorySubmitSlot = categoryEditor.querySelector('[data-vava-category-submit]');
      var currentCategoryLanguage = 'ar';
      var categoryCopy = {
        ar: { headingAdd: 'إضافة تصنيف أقسام', headingEdit: 'تحرير تصنيف الأقسام', intro: 'أدخل بيانات التصنيف باللغة المختارة.', name: 'اسم التصنيف', nameHelp: 'الاسم كما سيظهر في الموقع.', slug: 'الاسم اللطيف', slugHelp: '“slug” هو الرابط اللطيف للاسم، ويتكون عادةً من حروف صغيرة وأرقام وشرطات.', parent: 'التصنيف الأب', parentHelp: 'يمكن أن تكون التصنيفات هرمية، والتصنيف الأب مشترك بين اللغتين.', description: 'الوصف', none: 'بدون' },
        en: { headingAdd: 'Add section category', headingEdit: 'Edit section category', intro: 'Enter the category data in the selected language.', name: 'Category name', nameHelp: 'The name as it will appear on the site.', slug: 'Slug', slugHelp: 'The URL-friendly form, usually lowercase letters, numbers and hyphens.', parent: 'Parent category', parentHelp: 'Categories can be hierarchical. The parent is shared by both languages.', description: 'Description', none: 'None' }
      };
      function storeField(language, field, value) {
        var store = categoryEditor.querySelector('[data-vava-category-store="' + language + '-' + field + '"]');
        if (store) store.value = value;
      }
      function readField(language, field) {
        var store = categoryEditor.querySelector('[data-vava-category-store="' + language + '-' + field + '"]');
        return store ? store.value : '';
      }
      function saveVisibleCategoryFields() {
        categoryEditor.querySelectorAll('[data-vava-category-field]').forEach(function (field) { storeField(currentCategoryLanguage, field.getAttribute('data-vava-category-field'), field.value); });
        var parent = document.getElementById('vava-category-parent');
        if (parent) { storeField('ar', 'parent', parent.value); storeField('en', 'parent', parent.value); }
      }
      function loadVisibleCategoryFields(language) {
        categoryEditor.querySelectorAll('[data-vava-category-field]').forEach(function (field) {
          field.value = readField(language, field.getAttribute('data-vava-category-field'));
          if (field.getAttribute('data-vava-category-field') === 'name') field.required = language === 'ar';
        });
      }
      function translateCategoryScreen(language) {
        var en = language === 'en';
        var c = categoryCopy[language];
        document.body.classList.toggle('vava-category-language-en', en);
        document.body.classList.toggle('vava-category-language-ar', !en);
        categoryEditor.setAttribute('dir', en ? 'ltr' : 'rtl');
        if (categoryFields) categoryFields.setAttribute('dir', en ? 'ltr' : 'rtl');
        var heading = categoryEditor.querySelector('[data-vava-category-heading]');
        if (heading) heading.textContent = document.body.classList.contains('term-php') ? c.headingEdit : c.headingAdd;
        var intro = categoryEditor.querySelector('[data-vava-category-intro]');
        if (intro) intro.textContent = c.intro;
        ['name', 'slug', 'parent', 'description'].forEach(function (key) {
          var label = categoryEditor.querySelector('[data-vava-category-label="' + key + '"]');
          if (label) label.textContent = c[key];
          var help = categoryEditor.querySelector('[data-vava-category-help="' + key + '"]');
          if (help) help.textContent = c[key + 'Help'];
        });
        var parent = document.getElementById('vava-category-parent');
        if (parent && parent.options.length) parent.options[0].text = c.none;
        var submit = document.querySelector('#submit, #edittag input[type="submit"]');
        if (submit) submit.value = en ? (document.body.classList.contains('term-php') ? 'Update category' : 'Add category') : (document.body.classList.contains('term-php') ? 'تحديث التصنيف' : 'إضافة تصنيف');
      }
      categoryButtons.forEach(function (button) {
        button.addEventListener('click', function () {
          var language = button.getAttribute('data-vava-category-lang');
          if (language === currentCategoryLanguage) return;
          saveVisibleCategoryFields();
          currentCategoryLanguage = language;
          loadVisibleCategoryFields(language);
          categoryEditor.setAttribute('data-vava-category-language', language);
          categoryButtons.forEach(function (item) { var active = item === button; item.classList.toggle('is-active', active); item.setAttribute('aria-selected', active ? 'true' : 'false'); });
          translateCategoryScreen(language);
        });
      });
      var nativeName = document.querySelector('.term-name-wrap, .form-field.term-name-wrap');
      var nativeSlug = document.querySelector('.term-slug-wrap, .form-field.term-slug-wrap');
      var nativeParent = document.querySelector('.term-parent-wrap, .form-field.term-parent-wrap');
      var nativeDescription = document.querySelector('.term-description-wrap, .form-field.term-description-wrap');
      if (nativeName) nativeName.classList.add('vava-category-native-hidden');
      if (nativeSlug) nativeSlug.classList.add('vava-category-native-hidden');
      if (nativeParent) nativeParent.classList.add('vava-category-native-hidden');
      if (nativeDescription) nativeDescription.classList.add('vava-category-native-hidden');
      var categoryForm = document.getElementById('addtag') || document.getElementById('edittag');
      var nativeCategorySubmit = categoryForm ? categoryForm.querySelector('#submit, input[type="submit"]') : null;
      if (categorySubmitSlot && nativeCategorySubmit) {
        nativeCategorySubmit.classList.add('vava-category-primary-action');
        categorySubmitSlot.appendChild(nativeCategorySubmit);
        var nativeSubmitWrap = categoryForm.querySelector('p.submit');
        if (nativeSubmitWrap && !nativeSubmitWrap.querySelector('input[type="submit"]')) nativeSubmitWrap.hidden = true;
      }
      if (categorySubmitSlot && document.body.classList.contains('term-php')) {
        var nativeDelete = categoryForm ? categoryForm.querySelector('#delete-link, .edit-tag-actions .delete a, .edit-tag-actions a.delete') : null;
        if (nativeDelete) {
          nativeDelete.classList.add('vava-category-delete-action');
          nativeDelete.innerHTML = '<span class="dashicons dashicons-trash" aria-hidden="true"></span><span>حذف</span>';
          categorySubmitSlot.appendChild(nativeDelete);
        }
      }
      var nativeCategoryName = categoryForm ? categoryForm.querySelector('#tag-name, #name') : null;
      if (nativeCategoryName) nativeCategoryName.value = readField('ar', 'name');
      var visibleCategoryName = categoryEditor.querySelector('[data-vava-category-field="name"]');
      if (visibleCategoryName) visibleCategoryName.addEventListener('input', function () {
        if (currentCategoryLanguage === 'ar' && nativeCategoryName) nativeCategoryName.value = visibleCategoryName.value;
      });
      if (categoryForm) categoryForm.addEventListener('submit', function () {
        saveVisibleCategoryFields();
        var name = categoryForm.querySelector('#tag-name, #name');
        var slug = categoryForm.querySelector('#tag-slug, #slug');
        var parent = categoryForm.querySelector('#parent');
        var description = categoryForm.querySelector('#tag-description, #description');
        if (name) name.value = readField('ar', 'name') || readField('en', 'name');
        if (slug) slug.value = readField('ar', 'slug');
        if (parent) parent.value = readField('ar', 'parent');
        if (description) description.value = readField('ar', 'description');
      });
      translateCategoryScreen('ar');

      var nativeSuccess = document.querySelector('#message.updated, .notice.notice-success');
      if (nativeSuccess) {
        nativeSuccess.style.display = 'none';
        var toast = document.createElement('div');
        toast.className = 'vava-success-toast';
        toast.setAttribute('dir', 'rtl');
        toast.innerHTML = '<div class="vava-success-toast-wave"></div><button type="button" class="vava-success-toast-close" aria-label="إغلاق">×</button><div class="vava-success-toast-check"><svg viewBox="0 0 64 64" aria-hidden="true"><path d="M16 33l11 11 22-25"></path></svg></div><div class="vava-success-toast-content"><strong>تم تحديث التصنيف</strong><span>تم حفظ تغييرات التصنيف بنجاح.</span></div>';
        document.body.appendChild(toast);
        window.setTimeout(function () { toast.classList.add('is-visible'); }, 40);
        toast.querySelector('.vava-success-toast-close').addEventListener('click', function () { toast.classList.remove('is-visible'); window.setTimeout(function () { toast.remove(); }, 260); });
      }
    }

    var editor = document.querySelector('[data-vava-article-language]');
    if (!editor) return;
    var buttons = editor.querySelectorAll('[data-vava-article-language-button]');
    var panes = editor.querySelectorAll('[data-vava-article-language-pane]');
    var updateButton = editor.querySelector('[data-vava-article-update]');
    var form = document.getElementById('post');
    var currentLanguage = 'ar';
    var isPage = window.vavaArticleEditor && vavaArticleEditor.contentType === 'page';

    var copy = {
      ar: {
        pageTitle: 'تحرير المقال', update: 'تحديث', publish: 'نشر', remove: 'حذف', removeTitle: 'حذف المقال',
        categories: 'التصنيفات', image: 'الصورة البارزة', addCategory: '+ إضافة تصنيف جديد',
        allCategories: 'كل التصنيفات', mostUsed: 'الأكثر استخدامًا', parentCategory: 'التصنيف الأب',
        newCategory: 'اسم التصنيف الجديد', add: 'إضافة', setImage: 'تعيين الصورة البارزة', removeImage: 'إزالة الصورة البارزة',
        addMedia: 'إضافة وسائط', visual: 'مرئي', text: 'نص', savedTitle: 'تم تحديث المقال',
        savedMessage: 'تم حفظ التغييرات ونشرها.', close: 'إغلاق', words: 'كلمة', imageHint: 'انقر على الصورة للتعديل أو التحديث'
      },
      en: {
        pageTitle: 'Edit article', update: 'Update', publish: 'Publish', remove: 'Delete', removeTitle: 'Delete article',
        categories: 'Categories', image: 'Featured image', addCategory: '+ Add new category',
        allCategories: 'All categories', mostUsed: 'Most used', parentCategory: 'Parent category',
        newCategory: 'New category name', add: 'Add', setImage: 'Set featured image', removeImage: 'Remove featured image',
        addMedia: 'Add media', visual: 'Visual', text: 'Text', savedTitle: 'Article updated',
        savedMessage: 'Your changes have been saved and published.', close: 'Close', words: 'words', imageHint: 'Click the image to edit or update'
      }
    };

    if (isPage) {
      copy.ar.pageTitle = 'تحرير الصفحة'; copy.ar.removeTitle = 'حذف الصفحة'; copy.ar.savedTitle = 'تم تحديث الصفحة'; copy.ar.savedMessage = 'تم حفظ تغييرات الصفحة ونشرها.';
      copy.en.pageTitle = 'Edit page'; copy.en.removeTitle = 'Delete page'; copy.en.savedTitle = 'Page updated'; copy.en.savedMessage = 'The page changes have been saved and published.';
    }

    document.body.classList.add('vava-article-screen-ready');

    /* Native controls keep their WordPress behaviour, while VAVA owns their UI. */
    var sidebar = document.getElementById('postbox-container-1');
    if (sidebar) {
      sidebar.setAttribute('aria-label', 'إعدادات المقال');
      [
        ['categorydiv', 'التصنيفات', 'vava-side-categories'],
        ['postimagediv', 'الصورة البارزة', 'vava-side-image']
      ].forEach(function (item) {
        var box = document.getElementById(item[0]);
        if (!box) return;
        box.classList.add('vava-article-side-card', item[2]);
        box.setAttribute('data-vava-card-key', item[0] === 'categorydiv' ? 'categories' : 'image');
        var header = box.querySelector('.postbox-header');
        if (header) header.setAttribute('aria-hidden', 'true');
      });

      ['submitdiv', 'tagsdiv-post_tag'].forEach(function (id) {
        var hiddenBox = document.getElementById(id);
        if (hiddenBox) hiddenBox.setAttribute('aria-hidden', 'true');
      });
    }

    function setText(selector, value, root) {
      var node = (root || document).querySelector(selector);
      if (node && node.textContent !== value) node.textContent = value;
    }

    function translateEditorButtons(language) {
      var c = copy[language];
      panes.forEach(function (pane) {
        var paneLanguage = pane.getAttribute('data-vava-article-language-pane');
        var media = pane.querySelector('.insert-media');
        if (media) {
          var mediaText = media.querySelector('.wp-media-buttons-icon');
          media.textContent = c.addMedia;
          if (mediaText) media.insertBefore(mediaText, media.firstChild);
        }
        setText('.wp-switch-editor.switch-tmce', c.visual, pane);
        setText('.wp-switch-editor.switch-html', c.text, pane);
        var wordLabel = pane.querySelector('[data-vava-word-count="' + paneLanguage + '"]');
        if (wordLabel && wordLabel.parentNode) {
          wordLabel.parentNode.lastChild.textContent = ' ' + c.words;
        }
      });

      var tiny = document.querySelectorAll('.mce-btn[aria-label], .mce-btn[title]');
      var titles = {
        ar: { 'Bold':'عريض', 'Italic':'مائل', 'Bulleted list':'قائمة نقطية', 'Numbered list':'قائمة مرقمة', 'Blockquote':'اقتباس', 'Align left':'محاذاة لليسار', 'Align center':'توسيط', 'Align right':'محاذاة لليمين', 'Insert/edit link':'إضافة/تحرير رابط', 'Remove link':'إزالة الرابط', 'Undo':'تراجع', 'Redo':'إعادة', 'Toolbar Toggle':'المزيد من الأدوات' },
        en: { 'عريض':'Bold', 'مائل':'Italic', 'قائمة نقطية':'Bulleted list', 'قائمة مرقمة':'Numbered list', 'اقتباس':'Blockquote', 'محاذاة لليسار':'Align left', 'توسيط':'Align center', 'محاذاة لليمين':'Align right', 'إضافة/تحرير رابط':'Insert/edit link', 'إزالة الرابط':'Remove link', 'تراجع':'Undo', 'إعادة':'Redo', 'المزيد من الأدوات':'Toolbar Toggle' }
      };
      tiny.forEach(function (button) {
        ['aria-label', 'title'].forEach(function (attribute) {
          var value = button.getAttribute(attribute);
          if (value && titles[language][value]) button.setAttribute(attribute, titles[language][value]);
        });
      });
    }

    function translatePage(language) {
      currentLanguage = language;
      var c = copy[language];
      editor.setAttribute('dir', language === 'en' ? 'ltr' : 'rtl');
      document.body.classList.toggle('vava-article-language-en', language === 'en');
      document.body.classList.toggle('vava-article-language-ar', language === 'ar');
      setText('.vava-article-toolbar h1', c.pageTitle, editor);
      if (updateButton) updateButton.textContent = window.vavaArticleEditor && vavaArticleEditor.isPublished ? c.update : c.publish;
      var remove = editor.querySelector('.vava-article-delete');
      if (remove) {
        setText('span:last-child', c.remove, remove);
        remove.setAttribute('title', c.removeTitle);
        remove.setAttribute('aria-label', c.removeTitle);
      }
      var category = document.getElementById('categorydiv');
      var image = document.getElementById('postimagediv');
      if (category) category.setAttribute('data-vava-card-title', c.categories);
      if (image) image.setAttribute('data-vava-card-title', c.image);
      setText('#category-tabs a[href="#category-all"]', c.allCategories);
      setText('#category-tabs a[href="#category-pop"]', c.mostUsed);
      setText('#category-add-toggle', c.addCategory);
      setText('#category-add-submit', c.add);
      var categoryName = document.getElementById('newcategory');
      if (categoryName) categoryName.setAttribute('placeholder', c.newCategory);
      var categoryParent = document.getElementById('newcategory_parent');
      if (categoryParent && categoryParent.options.length) categoryParent.options[0].text = c.parentCategory;
      if (category && window.vavaArticleEditor && vavaArticleEditor.categoryNames) {
        category.querySelectorAll('input[name="post_category[]"]').forEach(function (input) {
          var label = input.closest('label');
          var names = vavaArticleEditor.categoryNames[input.value];
          if (label && names) {
            var textNode = Array.prototype.find.call(label.childNodes, function (node) { return node.nodeType === 3 && node.textContent.trim(); });
            if (textNode) textNode.textContent = ' ' + (names[language] || names.ar || textNode.textContent.trim());
          }
        });
      }
      var setThumbnail = document.getElementById('set-post-thumbnail');
      if (setThumbnail && !setThumbnail.querySelector('img') && setThumbnail.textContent !== c.setImage) setThumbnail.textContent = c.setImage;
      if (setThumbnail && setThumbnail.querySelector('img')) {
        var hint = setThumbnail.querySelector('.howto');
        if (hint) hint.textContent = c.imageHint;
        setThumbnail.setAttribute('aria-label', c.imageHint);
        setThumbnail.setAttribute('title', c.imageHint);
      }
      document.querySelectorAll('#postimagediv .howto').forEach(function (hint) { hint.textContent = c.imageHint; });
      setText('#remove-post-thumbnail', c.removeImage);
      translateEditorButtons(language);
    }

    function showSavedModal() {
      var old = document.querySelector('.vava-article-success-modal');
      if (old) old.remove();
      var c = copy[currentLanguage];
      var modal = document.createElement('section');
      modal.className = 'vava-success-toast';
      modal.setAttribute('role', 'status');
      modal.setAttribute('aria-live', 'polite');
      modal.setAttribute('dir', currentLanguage === 'en' ? 'ltr' : 'rtl');
      modal.innerHTML = '<div class="vava-success-toast-wave" aria-hidden="true"></div><button class="vava-success-toast-close" type="button">&times;</button><div class="vava-success-toast-check" aria-hidden="true"><svg viewBox="0 0 48 48"><path d="m13 25 8 8 15-18"/></svg></div><div class="vava-success-toast-content"><strong></strong><span></span></div>';
      modal.querySelector('strong').textContent = c.savedTitle;
      modal.querySelector('.vava-success-toast-content > span').textContent = c.savedMessage;
      var closeButton = modal.querySelector('.vava-success-toast-close');
      closeButton.setAttribute('aria-label', c.close);
      function close() { modal.classList.remove('is-visible'); window.setTimeout(function () { modal.remove(); }, 260); }
      closeButton.addEventListener('click', close);
      document.body.appendChild(modal);
      window.requestAnimationFrame(function () { modal.classList.add('is-visible'); });
      closeButton.focus();
    }

    function updateCount(language) {
      var pane = editor.querySelector('[data-vava-article-language-pane="' + language + '"]');
      var output = editor.querySelector('[data-vava-word-count="' + language + '"]');
      if (!pane || !output) return;
      var textarea = pane.querySelector('textarea.wp-editor-area');
      var text = textarea ? textarea.value.replace(/<[^>]*>/g, ' ').replace(/&nbsp;/g, ' ') : '';
      var words = text.trim() ? text.trim().split(/\s+/).length : 0;
      output.textContent = String(words);
    }

    function fitVisualEditor(language) {
      window.setTimeout(function () {
        var id = 'vava_article_content_' + language;
        var wrap = document.getElementById('wp-' + id + '-wrap');
        if (wrap) {
          wrap.style.width = '100%';
          var iframe = wrap.querySelector('iframe');
          if (iframe) {
            iframe.style.width = '100%';
            iframe.style.minHeight = '430px';
          }
        }
        if (window.tinyMCE) {
          var instance = window.tinyMCE.get(id);
          if (instance && instance.theme && typeof instance.theme.resizeTo === 'function') {
            instance.theme.resizeTo('100%', 430);
          }
        }
        window.dispatchEvent(new Event('resize'));
      }, 60);
    }

    buttons.forEach(function (button) {
      button.addEventListener('click', function () {
        var language = button.getAttribute('data-vava-article-language-button');
        editor.setAttribute('data-vava-article-language', language);
        buttons.forEach(function (item) {
          var active = item === button;
          item.classList.toggle('is-active', active);
          item.setAttribute('aria-selected', active ? 'true' : 'false');
        });
        panes.forEach(function (pane) {
          pane.classList.toggle('is-active', pane.getAttribute('data-vava-article-language-pane') === language);
        });
        translatePage(language);
        updateCount(language);
        fitVisualEditor(language);
      });
    });

    panes.forEach(function (pane) {
      var language = pane.getAttribute('data-vava-article-language-pane');
      var textarea = pane.querySelector('textarea.wp-editor-area');
      if (textarea) textarea.addEventListener('input', function () { updateCount(language); });
      updateCount(language);
    });

    fitVisualEditor('ar');
    translatePage('ar');

    var nativeNotice = document.querySelector('#message.updated, .notice-success');
    if (nativeNotice) nativeNotice.style.display = 'none';
    if (window.vavaArticleEditor && vavaArticleEditor.initialStatus === 'saved') {
      window.setTimeout(showSavedModal, 120);
    }

    var translationObserver = new MutationObserver(function () {
      window.clearTimeout(translationObserver.timer);
      translationObserver.timer = window.setTimeout(function () { translatePage(currentLanguage); }, 40);
    });
    ['categorydiv', 'postimagediv'].forEach(function (id) {
      var target = document.getElementById(id);
      if (target) translationObserver.observe(target, { childList: true, subtree: true });
    });

    if (updateButton && form) {
      updateButton.addEventListener('click', function () {
        if (window.tinyMCE) window.tinyMCE.triggerSave();
        var nativeButton = document.getElementById('publish') || document.getElementById('save-post');
        if (nativeButton) nativeButton.click();
        else form.submit();
      });
    }
  });
}());
