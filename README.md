# Asyntai AI Search for Moodle

An AI search bar for your Moodle site that understands what students mean, not only the words they typed.

A student types "where do I go when the fire alarm goes off" and the bar finds your Assembly points page, although not one of those words is in its title. It searches the courses you tick: course descriptions, pages, book chapters, labels, activity descriptions and the names of attached files.

Built for schools, colleges, universities and training providers using Moodle 4.2 or later.


## What it does

- **Understands intent, not only keywords.** Questions in plain words find the right page, chapter or file.
- **Searches your courses.** Tick the courses the bar may read. Nothing is read until you do.
- **Works in the student's own language.** The bar's labels follow the page language, and questions in any language find content in any language.
- **Sits in the top navigation bar.** Or in place of the theme's own search box, or inside any element you choose.
- **Shown to signed-in users only, by default.** The bar searches your course material, so visitors who are not signed in do not see it unless you say otherwise.
- **Student data is never read.** Grades, submissions, forum posts, quiz answers and personal details all stay inside Moodle.


## Installation

1. Go to **Site administration → Plugins → Install plugins**.
2. Upload the ZIP file and install it.
3. Go to **Site administration → Plugins → Local plugins → Asyntai AI Search**.
4. Press **Connect Asyntai** and sign in, or create a free account.
5. Under **Course content**, tick **Let Asyntai read the ticked courses**, tick the courses, and press **Save course content**.
6. The bar switches on by itself a few minutes later, once the courses are read. The settings page tells you when it is live.

Don't have an Asyntai account yet? Create one for free at [Asyntai.com](https://asyntai.com/auth).


## Where the bar goes

- **In the top navigation bar** (default). Shown on every page, next to the user menu. Works with Boost, Classic and the themes built on them.
- **In place of the theme's own search box.** Needs a search box on the page: global search in the navigation bar, or the course search box. Pressing Enter without picking a result still opens the usual search page.
- **Inside an element I choose.** Give a CSS selector and the bar is rendered inside it.


## Course content

Nothing is read until you tick a course. You can untick a course at any time and it stops being read; switching the read off deletes the key and removes the content from Asyntai.

**What is read:** course names and descriptions, pages, book chapters, labels, activity descriptions, and the names of attached files.

**What is never read:** anything belonging to a student. Grades, submissions, forum posts, quiz answers and personal details all stay inside Moodle.

Saving switches on Moodle web services and the REST protocol, and creates one token. That token belongs to a service holding a single read-only function, so it cannot reach anything else in Moodle. Disconnecting the plugin deletes the token.


## External service

This plugin connects to Asyntai, a hosted AI search service, because the search index and the AI model run there rather than on your server.

- On connect: your site address and your Moodle account email.
- After you tick courses: the material in those courses, read through the token above.
- On every search: the words the visitor typed, so results can be returned.
- Every ten minutes, from your server: your Asyntai site id, to ask whether the bar may be shown.

An Asyntai account is required. A free account is enough to run the bar through this plugin. Every plan includes a monthly allowance of AI replies, shared with the Asyntai chat assistant, and each search uses one. When the allowance runs out the bar switches itself off until the month resets. The settings page always shows how much you have left.

Terms of Use: https://asyntai.com/terms-and-conditions/
Privacy Policy: https://asyntai.com/privacy-policy/


## Requirements

Moodle 4.2 to 5.1, PHP 8.1 or later. Your server must be able to make outgoing HTTPS requests to asyntai.com.


## Need help?

Email us at hello@asyntai.com, or read the documentation at https://asyntai.com/documentation/moodle-ai-search/.
