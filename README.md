# Drupal 8 Subscribe Module
By: *Ahmed Raza*
Version: *8.x-1.0 dev*
- Config: Config > People > Subscribe
- Provides a subscribe block.
- Emails for subscription / unsubcription along with confirmation link.
- Username field enable/disable (According to requirements)
- List of subscribers.
- Export to xls functionality.

Want to add subscriber from your custom form, having name and email fields? Use this class; 
`use Drupal\subscribe\Subscribe;` 
Then call this method to subscribe user on your custom form submit. 
`Subscribe::subscribeMe($username, $email, $status)` 
```
$username is the name of user. 
$email is the email. 
$status is whether you want to add this subscriber as confirm by default or not. Simply set it to `1` or `0` according to your requirements. 
```
If you set the `$status` to `0` and want to send user an email with the confirmation link, call this method after `subscribeMe`.
`Subscribe::confirmationEmail($username, $email)`
This will send the end user an email with the confirmation link in it. *Note:* If the user already exists but has not confirmed the email before means his/her `$status` is `0` this email method will send them confirmation email again. 
