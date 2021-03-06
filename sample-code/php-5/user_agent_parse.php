<?php
require ("config.php");

# Where will the request be sent to
$url = 'https://api.whatismybrowser.com/api/v2/user_agent_parse';

# -- Set up HTTP Headers
$headers = [
    'X-API-KEY: '.$api_key,
];

# -- Set up the request data
$post_data = array(
    "user_agent" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3282.167 Safari/537.36",
);

# -- create a CURL handle containing the settings & data
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL, $url);
curl_setopt($ch,CURLOPT_POST, true);
curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($post_data));
curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

# -- Make the request
$result = curl_exec($ch);
$curl_info = curl_getinfo($ch);
curl_close($ch);

# -- Try to decode the api response as json
$result_json = json_decode($result);
if ($result_json === null) {
    echo "Couldn't decode the response as JSON\n";
    exit();
}

# -- Check that the server responded with a "200/Success" code
if ($curl_info['http_code'] != 200) {
    echo "Didn't receive a 200 Success response from the API\n";
    echo "Instead, there was a ".$curl_info['http_code']." code\n";
    echo "The message was: ".$result_json->result->message."\n";
    exit();
}

# -- Check the API request was successful
if ($result_json->result->code != "success") {
    throw new Exception("The API did not return a 'success' response. It said: result: ".$result_json->result.", message_code: ".$result_json->message_code.", message: ".$result_json->message_code);
    exit();
}

# Now you have "$result_json" and can store, display or process any part of the response.

# -- print the entire json dump for reference
var_dump($result_json);

# -- Copy the data to some variables for easier use
$parse = $result_json->parse;
$version_check = $result_json->version_check;

# Now you can do whatever you need to do with the parse result
# Print it to the console, store it in a database, etc
# For example - printing to the console:

if ($parse->is_abusive === True) {
    echo "BE CAREFUL - this user agent seems abusive\n";
    # This user agent contains one or more fragments which appear to
    # be an attempt to compromise the security of your system
}

if ($parse->simple_software_string) {
    echo $parse->simple_software_string."\n";
}
else {
    echo "Couldn't figure out what software they're using\n";
}

if ($parse->simple_sub_description_string) {
    echo $parse->simple_sub_description_string."\n";
}

if ($parse->simple_operating_platform_string) {
    echo $parse->simple_operating_platform_string."\n";
}

if ($version_check) {
    # Your API account has access to version checking information

    if ($version_check->is_checkable === True) {
        if ($version_check->is_up_to_date === True) {
            echo $parse->software_name." is up to date\n";
        }
        else {
            echo $parse->software_name." is out of date\n";

            if ($version_check->latest_version) {
                echo "The latest version is ".join(".", $version_check->latest_version)."\n";
            }

            if ($version_check->update_url) {
                echo "You can update here: ".$version_check->update_url."\n";
            }
        }
    }
}

# Refer to:
# https://developers.whatismybrowser.com/api/docs/v2/integration-guide/#user-agent-parse-field-definitions
# for more fields you can use
