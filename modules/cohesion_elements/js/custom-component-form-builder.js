(function($) {
  "use strict";

  function download(filename, text) {
      var element = document.createElement('a');
      element.setAttribute('href', 'data:application/json;charset=utf-8,' + encodeURIComponent(text));
      element.setAttribute('download', filename);
      element.style.display = 'none';
      document.body.appendChild(element);
      element.click();
      document.body.removeChild(element);
  }

  // Start file download.
  document.getElementById("download_json_values").addEventListener("click", function(e){
    e.target.disabled=true;
    setTimeout(function(){ e.target.disabled=false }, 500)
    // Generate download of hello.txt file with some content
    var text = JSON.stringify(JSON.parse(document.getElementById("config_layout_canvas_modelAsJson").value), null, 4);
    var filename = "form.json";
    download(filename, text);
  }, false);


})(jQuery);
