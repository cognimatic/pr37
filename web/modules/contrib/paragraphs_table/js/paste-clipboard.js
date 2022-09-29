(function ($, Drupal) {
  Drupal.behaviors.pasteParagraphsClipboard = {
    attach: function attach(context, settings) {
      $(".paste-paragraphs-clipboard").on("click", function () {
        const id_table = $(this).data("table");
        const tab = "\t";
        const breakLine = "\n";
        navigator.clipboard.readText()
          .then(text => {
            let items = text.split(breakLine);
            let data = [];
            $.each(items, function (key, val) {
              data[key] = $.trim(val).split(tab);
            });
            $('table#' + id_table + ' > tbody  > tr').each(function (row, tr) {
              if(row in data){
                let removeFirstCol = 0;
                if($(this).hasClass('draggable')){
                  removeFirstCol = 1;
                }
                $(this).find('td').each(function (col, td) {
                  col -= removeFirstCol;
                  if(col in data[row]){
                    $(this).find("input").val(data[row][col]);
                    //if select check have value
                    const exists = 0 != $(this).find('select').length;
                    if(exists){
                      let that = $(this);
                      $.each($(this).find("select").prop("options"), function (i, opt) {
                        if(opt.textContent == data[row][col] || opt.value == data[row][col]){
                          that.find("select").val(opt.value);
                        }
                      })
                    }
                  }
                });
              }
            });
          })
          .catch(() => {
            console.log('Failed to read from clipboard.');
          });
      });

    }
  };
})(jQuery, Drupal);
