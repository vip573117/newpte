$(document).ready(function () {
  // $('.left-tab').on('click', '.item', function (e) {
  //   e.stopPropagation()
  //   var $item = $(e.currentTarget)
  //   var $anoitem = $item.siblings()
  //   var lesson = $item.data('lesson')
  //   $item.addClass('active')
  //   $anoitem.removeClass('active')
  //   $('.lesson' + lesson + '').siblings().hide()
  //   $('.lesson' + lesson + '').show()
  // })


  $('.list').on('click', '.item', function (e) {
    e.stopPropagation()
    var $item = $(e.currentTarget)
    var $anoitem = $item.siblings()
    var wrap = $item.data('wrap')
    if (wrap == 1) {
      $('.create').hide()
    } else {
      $('.create').show()
    }
    $item.addClass('active')
    $anoitem.removeClass('active')
    $('.check-wrap' + wrap + '').siblings().hide()
    $('.check-wrap' + wrap + '').show()
  })

  // 迟到圆
  var percent1 = parseInt($('.circle1 .mask .percent').text());
  var baseColor1 = $('.circle1').css('background-color');
  if (percent1 <= 50) {
    $('.circle1 .circle-bar-right').css('transform', 'rotate(' + (percent1 * 3.6) + 'deg)');
  } else {
    $('.circle1 .circle-bar-right').css({
      'transform': 'rotate(0deg)',
      'background-color': baseColor1
    });
    $('.circle1 .circle-bar-left').css('transform', 'rotate(' + ((percent1 - 50) * 3.6) + 'deg)');
  }

  // 请假圆
  var percent2 = parseInt($('.circle2 .mask .percent').text());
  var baseColor2 = $('.circle2').css('background-color');

  if (percent2 <= 50) {
    $('.circle2 .circle-bar-right').css('transform', 'rotate(' + (percent2 * 3.6) + 'deg)');
  } else {
    $('.circle2 .circle-bar-right').css({
      'transform': 'rotate(0deg)',
      'background-color': baseColor2
    });
    $('.circle2 .circle-bar-left').css('transform', 'rotate(' + ((percent2 - 50) * 3.6) + 'deg)');
  }

  // 缺勤圆
  var percent3 = parseInt($('.circle3 .mask .percent').text());
  var baseColor3 = $('.circle3').css('background-color');

  if (percent3 <= 50) {
    $('.circle3 .circle-bar-right').css('transform', 'rotate(' + (percent3 * 3.6) + 'deg)');
  } else {
    $('.circle3 .circle-bar-right').css({
      'transform': 'rotate(0deg)',
      'background-color': baseColor3
    });
    $('.circle3 .circle-bar-left').css('transform', 'rotate(' + ((percent3 - 50) * 3.6) + 'deg)');
  }

  // 出勤圆
  var percent4 = parseInt($('.circle4 .mask .percent').text());
  var baseColor4 = $('.circle4').css('background-color');

  if (percent4 <= 50) {
    $('.circle4 .circle-bar-right').css('transform', 'rotate(' + (percent4 * 3.6) + 'deg)');
  } else {
    $('.circle4 .circle-bar-right').css({
      'transform': 'rotate(0deg)',
      'background-color': baseColor4
    });
    $('.circle4 .circle-bar-left').css('transform', 'rotate(' + ((percent4 - 50) * 3.6) + 'deg)');
  }


})