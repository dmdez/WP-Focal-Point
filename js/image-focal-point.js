(function($) {

  var imageFocalGrid = function(data, options) {

    var defaults = {
      gridSize: 10,
      focalActiveClass: 'show-focal'
    };

    var focalGrid = this;
    var settings = $.extend(defaults, options);

    focalGrid.blocks = [];

    focalGrid.init = function() {     

      var button = $('<div class="imgedit-focal"></div>');

      $.extend(focalGrid, {
        buttonMenu   : $('.imgedit-menu'),
        imageWrapper : $('.imgedit-crop-wrap'),
        image        : $('.imgedit-crop-wrap img')
      });

      $('.imgedit-menu').prepend(button);

      button.click(focalButtonClick);
      focalGrid.image.load(getImageProperties);
    };

    var getImageProperties = function($image) {
      var width = focalGrid.image.width();
      var height = focalGrid.image.height();
      focalGrid.blockWidth = width / settings.gridSize;
      focalGrid.blockHeight = height / settings.gridSize;
      imagePropertiesReady();
    };

    var imagePropertiesReady = function() {
      focalGrid.wrap = $('<span />', { style: 'display: inline-block; postion: relative;' });
      focalGrid.image.wrap(focalGrid.wrap);
      for ( var x=0; x < settings.gridSize; x++ ) {
        for ( var y=0; y < settings.gridSize; y++ ) {
          buildBlocks(x, y);
        }
      }
    };

    var buildBlocks = function(x, y) {
      var $block = $('<div class="focal-block" />');

      $block
        .height(focalGrid.blockHeight)
        .width(focalGrid.blockWidth)
        .css({
          'left' : (x * focalGrid.blockWidth) + 'px',
          'top'  : (y * focalGrid.blockHeight) + 'px'
        })
        .click($.proxy(onFocalBlockClick, $block, [x, y]));

      focalGrid.image.after($block);

      if ( x == data.x && y == data.y )
        $block.addClass('active');

      focalGrid.blocks.push($block);
    };

    var onFocalBlockClick = function(props) {
      var $block = $(this);

      $.each(focalGrid.blocks, function() {
        $(this).removeClass('active');
      });

      $block.addClass('active');
      focalGrid.imageWrapper.removeClass(settings.focalActiveClass);
      focalGrid.wrap.append('<div class="focal-loading">Loading...</div>');

      $.ajax({
        url: ajaxurl,
        dataType: 'JSON',
        data: {
          action: 'image-focalpoint',
          focal_x: props[0],
          focal_y: props[1],
          postid: data.postid
        },
        success: $.proxy(ajaxSuccess, props)
      });
    };

    var ajaxSuccess = function() {
      $('.imgedit-wrap .updated').remove();
      focalGrid.wrap.find('.focal-loading').remove();
      var $status = $('<div class="updated"><p>Focal Point Added (' + this[0] + 'x' + this[1] + ')</p></div>');
      $('.imgedit-wrap').prepend($status);
      setTimeout(function() {
        $status.fadeOut();
      }, 3000);
    };

    var focalButtonClick = function() {
      focalGrid.imageWrapper.toggleClass(settings.focalActiveClass);
    };

    focalGrid.init();

  };

  $.extend(window, { imageFocalGrid: imageFocalGrid });




})(jQuery);