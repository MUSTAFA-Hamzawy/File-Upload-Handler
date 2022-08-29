<?php

class FileUpload
{
  // Properties
  private $fileName;
  private $fileType;
  private $fileSize;
  private $errorCode;
  private $tmpPath;
  private $fileExtension;
  private $allowedExtensions = [
    'jpg', 'jpeg', 'png', 'gif', 'pdf',
    'docx', 'doc', 'txt', 'ppt', 'pptx',
    'xls', 'xlsx','webp'
  ];

  // Methods
  public function __construct(array $fileInfo){
    if (!is_null($fileInfo))
    {
      $this->resultMessages = array();
      $this->fileName    = $fileInfo['name'];
      $this->fileType    = $fileInfo['type'];
      $this->tmpPath     = $fileInfo['tmp_name'];
      $this->fileSize    = $fileInfo['size'];
      $this->errorCode = $fileInfo['error'];

      $this->prepareFileName($this->fileName);
    }
  }

  // To separate the extension from the file name, and hash the name of the file
  private function prepareFileName(){
    $dotPosition = strrpos($this->fileName, '.' );
    $this->fileExtension = strtolower(substr($this->fileName, $dotPosition+1));
    $this->fileName = substr(md5(time()), 0, 25);
  }

  // To check whether the file extension is allowed or not
  private function isAllowedExtension(){
    return in_array($this->fileExtension, $this->allowedExtensions);
  }

  // to get the max file size allowed using the info of the php.ini file
  private function getMaxAllowedSize(){
    $maxFileSize = ini_get('upload_max_filesize');
    $length = strlen($maxFileSize);
    $unit = strtolower($maxFileSize[ $length - 1]);
    $maxFileSize = substr($maxFileSize, 0, $length - 1);
    switch ($unit){
      case 'k':
        $maxFileSize *= 1024;
        break;
      case 'm':
        $maxFileSize *= pow(1024, 2);
        break;
      case 'g':
        $maxFileSize *= pow(1024, 3);
        break;
    }
    return $maxFileSize;
  }

  private function isSizeAllowed(){
    return $this->fileSize <= $this->getMaxAllowedSize();
  }

  private function isImage(){
    return preg_match('/image/i', $this->fileType);
  }

  public function getFileFullName(){
    return $this->fileName . '.' . $this->fileExtension;
  }

  private function handleErrorCode(){

    switch ($this->errorCode)
    {
      case UPLOAD_ERR_OK:
        return true;
        break;
      case UPLOAD_ERR_NO_FILE:
        trigger_error("No File Uploaded.", E_USER_ERROR);
        break;
      case UPLOAD_ERR_PARTIAL:
        trigger_error("The uploaded file was only partially uploaded.", E_USER_ERROR);
        break;
      case UPLOAD_ERR_NO_TMP_DIR:
        trigger_error("Missing a temporary folder to upload.", E_USER_ERROR);
        break;
    }
  }

  // To check whether the uploads directory is writable or not
  private function checkWritePermission($uploadFolder)
  {
      if(! is_writable($uploadFolder) )
        trigger_error("Upload directory is non-writable", E_USER_ERROR);

      return true;
  }

  public function upload(){

    if (!$this->handleErrorCode())
      return false;

    if (!$this->isAllowedExtension()){
      trigger_error("Files of type {$this->fileExtension} are not allowed.", E_USER_WARNING);
      return false;
    }
    else if (!$this->isSizeAllowed()){
      trigger_error("File size exceeds the max allowed size.", E_USER_WARNING);
      return false;
    }
    else {

      if ($this->isImage() && $this->checkWritePermission(IMAGES_UPLOADS))
          move_uploaded_file($this->tmpPath, IMAGES_UPLOADS . DIRECTORY_SEPARATOR . $this->getFileFullName());
      else{

        if ($this->checkWritePermission(DOCS_UPLOADS))
          move_uploaded_file($this->tmpPath, DOCS_UPLOADS . DIRECTORY_SEPARATOR . $this->getFileFullName());
      }

    }

      return true;
  }
}
