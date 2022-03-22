jQuery(document).ready(function ($) {
  const $triggerBuildButton = $('#menu-trigger-js');

  if (!$triggerBuildButton) return;

  $triggerBuildButton.click(function () {
    const $triggerUrl = $(this).data('trigger-url');

    $.ajax({
      method: 'get',
      url: $triggerUrl,
      success: (result) => {
        alert(result);
      },
    });
  });
});
