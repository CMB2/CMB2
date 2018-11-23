// Gutenberg is used && window.tinymce is set
if (wp.data && window.tinymce) {
  wp.data.subscribe(function () {
    // the post is currently being saved && we have tinymce editors
    if (wp.data.select( 'core/editor' ).isSavingPost() && window.tinymce.editors) {
      for (var i = 0; i < tinymce.editors.length; i++) {
        tinymce.editors[i].save();
      }
    }
  });
}
