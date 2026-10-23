# Admin

Filament at `/admin`. Every admin can do everything. Sign-in is email and
password, with a "Forgot password" email link.

| Group    | Screen             | What it does                                                                                                                                              |
| -------- | ------------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Content  | Regular activities | Title, address, markdown body, day and time (they drive the next-session labels), price, age range, contact details, image, draft, publish from, position |
| Content  | Pages              | Admin-created pages at `/{slug}`. The Contact template adds the enquiry form under the body                                                               |
| Hall     | Spaces             | The hireable spaces. Bookable ones show as chips on the home page. Drag to reorder                                                                        |
| Hall     | Bookings           | Hirer, spaces, times, status and an optional payment. Not yet used by the public site                                                                     |
| Hall     | Hirers, Payments   | People who hire the hall, and one payment per booking (entered in pounds, stored in pence)                                                                |
| Inbox    | Enquiries          | Every contact-form enquiry. Viewing one marks it read; Reply opens your mail app                                                                          |
| Settings | Admin users        | Add an admin, set a password. You can't delete yourself or the last admin                                                                                 |

The dashboard shows counts, and a note about what is not edited here: the
built-in pages (in `resources/js/pages/site`), articles (`content/articles`)
and the Repair Cafe photos (`public/images/repair-cafe`).

From the command line, `php artisan swvh:admin you@example.com` creates an
admin or resets their password.
