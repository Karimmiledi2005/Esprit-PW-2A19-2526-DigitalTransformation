Test script for referral code validation

Run tests (requires PHP CLI with cURL and a running local server hosting the app):

```bash
php scripts/test_referral.php http://localhost/gestion_user/view/FrontOffice/registre.php
```

This script performs 4 registration attempts:
- valid_upper: referral code in uppercase
- valid_lower: referral code in lowercase (should be normalized)
- invalid_code: non-existing referral code (should return an error)
- empty: no referral code (normal registration)

Inspect responses to confirm that the referral update happens and invalid codes are rejected with a clear message.
