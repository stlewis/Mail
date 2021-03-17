<?php 

/**
* A PHP class for sending email messages which includes capabilities for sending plaintext, html and multipart messages, including support for attachments.
* 
* While none of the capabilities of this class exceed those of more established packages such as PEARMail, my hope is that this class offers the advantage of
* portability, since not every setup has the ability to install PEAR packages.  Also, this class is ideal for embedding in other systems, which I have done in
* the case of my MVC framework, DirtyMVC.
*
* @author Steve Lewis <steve@thoughtsandrambles.com>
* @version 0.1.0
* @package Mail
*
**/
  class Mail{
    /**
    * @var string $to The email address, (or addresses, if comma separated), of the person to which this email should be sent.
    **/
    public $to;

    /**
    * @var string $from The email address that this mail will be delivered from. Bear in mind that this can be anything, but that if the email 
    *                   domain doesn't match the actual domain the message was sent from, some email clients will reject the message as spam.
    **/ 
    public $from;
    
    /**
    * @var string $subject The subject line of the email
    **/ 
    public $subject;

    /**
    * @var string $text_content The plaintext version of the message to be sent.
    **/ 
    public $text_content;

    /**
    * @var string $html_content The HTML version of the message to be sent.
    **/
    public $html_content;
    
    /**
    * @var string $body The complete body of the email that will be sent, including all mixed content.
    **/ 
    private $body;

    /**
    * @var array $attachments  An array of file paths pointing to the attachments that should be included with this email.
    **/
    private $attachments;
    
    /**
    * @var array $headers An array of the headers that will be included in this email.
    **/ 
    private $headers;

    /**
    * @var string $header_string The string, (and therefore final), representation of the headers for this email message.
    **/ 
    private $header_string;

    /**
    * @var string $boundary_hash The string that acts as a separator between the various mixed parts of the email message.
    **/  
    private $boundary_hash;

    /**
    * @var boolean $sent Whether or not this email message was successfully sent.
    **/
    private $sent;

    
    /**
    * Upon initialization of a Mail object, you have to pass it certain vital pieces of information.
    *
    * At a minimum, an email must consist of a receiver address, a sender address, and a subject.
    * The body can be left blank.
    **/
    public function __construct($to, $from, $subject, $text_content = "", $html_content = ""){
      $this->to            = $to;
      $this->from          = $from;
      $this->subject       = $this->convert_utf8($subject);
      $this->text_content  = $text_content;
      $this->html_content  = $html_content;
      $this->body          = "";
      $this->attachments   = array();
	  $this->base64_attachments   = array();
      $this->headers       = array();
      $this->boundary_hash = md5(date('r', time()));
    }

    /**
    * The send() method processes all headers, body elements and attachments and then actually sends the resulting final email.
    **/
    public function send(){
      $this->prepare_headers();      
      $this->prepare_body();
      if(!empty($this->attachments)){
        $this->prepare_attachments();  
      }
      if(!empty($this->base64_attachments)){
        $this->prepare_base64_attachments();  
      }
      $this->sent = mail($this->to, $this->subject, $this->body, $this->header_string);
      return $this->sent;
    }

    /**
    * This method allows the user to add a new header to the message
    * @param string $header The text for the header the user wants to add. Note that this string must be a properly formatted email header.
    **/
    public function add_header($header){
      $this->headers[] = $header;
    }

    /**
    * Add a filepath to the list of files to be sent with this email.
    * @param string $file The path to the file that should be sent.
    **/
    public function add_attachment($file){
      $this->attachments[] = $file;
    }
    public function add_base64_attachment($name,$data){
      $this->base64_attachments[] = array('name'=>$name, 'data'=>$data);
    }
    private function prepare_body(){
      $this->body .= "--PHP-mixed-{$this->boundary_hash}\n";
      $this->body .= "Content-Type: multipart/alternative; boundary=\"PHP-alt-{$this->boundary_hash}\"\n\n";
      if(!empty($this->text_content)) $this->prepare_text();
      if(!empty($this->html_content)) $this->prepare_html();
      $this->body .= "--PHP-alt-{$this->boundary_hash}--\n\n";
    }

    private function prepare_headers(){
      $this->set_default_headers();
      $this->header_string = implode(PHP_EOL, $this->headers).PHP_EOL;
    }

    private function set_default_headers(){
      $this->headers[] = 'MIME-Version: 1.0';
      $this->headers[] = "From: {$this->from}";
      # We'll assume a multi-part message so that we can include an HTML and a text version of the email at the
      # very least. If there are attachments, we'll be doing the same thing.
      $this->headers[] = "Content-type: multipart/mixed; boundary=\"PHP-mixed-{$this->boundary_hash}\"";
    }

    private function prepare_base64_attachments(){
      foreach($this->base64_attachments as $attachment){

        $this->body .= "--PHP-mixed-{$this->boundary_hash}\n";
        $this->body .= "Content-Type: application/octet-stream; name=\"{$attachment['name']}\"\n";
        $this->body .= "Content-Transfer-Encoding: base64\n";
        $this->body .= "Content-Disposition: attachment\n\n";
        $this->body .= chunk_split($attachment['data']);
        $this->body .= "\n\n";
      }
      $this->body .= "--PHP-mixed-{$this->boundary_hash}--\n\n";
    }
	
    private function prepare_attachments(){
      foreach($this->attachments as $attachment){
        $file_name  = basename($attachment);

        $file_name  = $this->convert_utf8($file_name);
        $this->body .= "--PHP-mixed-{$this->boundary_hash}\n";
        $this->body .= "Content-Type: application/octet-stream; name=\"{$file_name}\"\n";
        $this->body .= "Content-Transfer-Encoding: base64\n";
        $this->body .= "Content-Disposition: attachment\n\n";
        $this->body .= chunk_split(base64_encode(file_get_contents($attachment)));
        $this->body .= "\n\n";
      }
      $this->body .= "--PHP-mixed-{$this->boundary_hash}--\n\n";
    }
	

    private function prepare_text(){
      $this->body .= "--PHP-alt-{$this->boundary_hash}\n";
      $this->body .= "Content-Type: text/plain; charset=\"utf-8\"\n";
      $this->body .= "Content-Transfer-Encoding: base64\n\n";
      $this->body .= chunk_split(base64_encode($this->text_content))."\n\n";
    }

    private function prepare_html(){
      $this->body .= "--PHP-alt-{$this->boundary_hash}\n";
      $this->body .= "Content-Type: text/html; charset=\"utf-8\"\n";
      $this->body .= "Content-Transfer-Encoding: base64\n\n";
      $this->body .= chunk_split(base64_encode($this->html_content))."\n\n";
    }
    //convert to  utf8 
    private function convert_utf8($subject){
        return '=?UTF-8?B?'.base64_encode($subject).'?=';
    }

  }
