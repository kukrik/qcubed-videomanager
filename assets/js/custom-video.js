// $(document).ready(function () {
//     $(".control-scrollpad").slimscroll({height: 'auto'});
// });

// https://ckeditor.com/docs/ckeditor4/latest/guide/dev_file_browser_api.html

// Helper function to get parameters from the query string.
function getUrlParam( paramName ) {
    var reParam = new RegExp( '(?:[\?&]|&)' + paramName + '=([^&]+)', 'i' );
    var match = window.location.search.match( reParam );

    return ( match && match.length > 1 ) ? match[1] : null;
}

// Define an array of allowed file extensions
//const allowedExtensions = ['jpg', 'jpeg', 'bmp', 'png', 'webp', 'gif'];

// Function to check if a file has a valid extension
// function isFileExtensionAllowed(filename) {
//     const ext = filename.split('.').pop().toLowerCase();
//     return allowedExtensions.includes(ext);
// }