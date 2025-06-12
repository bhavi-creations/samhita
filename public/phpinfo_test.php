<?php
echo "allow_url_fopen: " . (ini_get('allow_url_fopen') ? 'On' : 'Off') . "<br>";
echo "Is public/uploads/marketing_persons/ writable? " . (is_writable(ROOTPATH . 'public/uploads/marketing_persons/') ? 'Yes' : 'No') . "<br>";
echo "Is public/uploads/marketing_persons/ readable? " . (is_readable(ROOTPATH . 'public/uploads/marketing_persons/') ? 'Yes' : 'No') . "<br>";
// phpinfo(); // You can uncomment this for full phpinfo, then search for allow_url_fopen
?>