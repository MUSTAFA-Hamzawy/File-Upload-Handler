# File Upload Handler



    // set your image/file upload dir path
    define("IMAGES_UPLOADS", "");
    define("DOCS_UPLOADS", "");
    
    // Example
    $upload = new new FileUpload($_FILES['image']);
    if ($upload->upload())
    echo "File has been Uploaded Successfully."
