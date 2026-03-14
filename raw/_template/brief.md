# Edition Brief — [Edition Title]
# Slug: YYYY-MM-DD-xx-type
# Prepared by: [Editor Name]
# Date prepared: YYYY-MM-DD

---
> INSTRUCTIONS FOR THE EDITOR:
> Fill in every section below for the pages listed in meta.json.
> Use clear, complete sentences — not bullet notes.
> Stats must include units. Quotes must include speaker attribution.
> Leave a field BLANK rather than writing placeholder text.
> Sections not listed in meta.json → pages can be deleted from this file.
---


## COVER

<!-- What this page does: First impression. Make them stop and read. -->

headline: "Your Bold Headline Here — Max 7 Words"
subheadline: "One sentence that makes them want to turn the page."
cover_image: assets/cover-bg.jpg
edition_badges:
  - "🔥 Today"
  - "⚡ Inspire"
  - "🚀 Grow"
inside_preview:
  - icon: "🌍"
    label: "Trending News"
  - icon: "⭐"
    label: "Success Story"
  - icon: "💼"
    label: "Careers"
  - icon: "🎵"
    label: "Entertainment"


## WELCOME

<!-- What this page does: Help them feel they belong here. -->

headline: "Welcome to Your Edition"
opening_line: "Write one powerful line that speaks directly to the reader — 'You're not here by accident.'"
mission_statement: "2-3 sentences about what KandaNews Africa is here to do and why it matters to this reader specifically."
value_pillars:
  - icon: "🌍"
    title: "Know"
    description: "Stay ahead of what matters in your world."
  - icon: "🚀"
    title: "Grow"
    description: "Build skills, mindset, and opportunity every day."
  - icon: "💡"
    title: "Do"
    description: "Take the step from inspiration to action."
closing_line: "This edition — every word of it — was built for you."


## TRENDING

<!-- What this page does: Ground them in reality. What's happening right now. -->

country: "Uganda"
country_flag: "🇺🇬"
section_subtitle: "What's happening in the Pearl of Africa"

stories:
  - category: "Politics"
    category_emoji: "🏛️"
    headline: "Full story headline here"
    body: "2-3 sentences of story body. Who, what, where, when, why. Keep it sharp."
    source: "Source Name"
    time_ago: "3 hours ago"

  - category: "Business"
    category_emoji: "💼"
    headline: "Full story headline here"
    body: "2-3 sentences of story body."
    source: "Source Name"
    time_ago: "5 hours ago"

  - category: "Education"
    category_emoji: "🎓"
    headline: "Full story headline here"
    body: "2-3 sentences of story body."
    source: "Source Name"
    time_ago: "8 hours ago"

  - category: "Tech"
    category_emoji: "💻"
    headline: "Full story headline here"
    body: "2-3 sentences of story body."
    source: "Source Name"
    time_ago: "1 day ago"


## DID-YOU-KNOW

<!-- What this page does: Surprise and expand their thinking. Lead with the number. -->

headline: "Did You Know?"
subtitle: "Facts that will change how you see the world"

facts:
  - number: "65%"
    statement: "of Africa's population is under 35 — the youngest continent on earth."
    icon: "👥"

  - number: "54"
    statement: "African countries, but only 4% of global internet infrastructure is based here."
    icon: "🌐"

  - number: "$3 trillion"
    statement: "— Africa's projected GDP by 2030, driven by its youth workforce."
    icon: "📈"

  - number: "2,000+"
    statement: "distinct languages are spoken across the African continent."
    icon: "🗣️"

  - number: "40%"
    statement: "of Africa's startups were founded by people under 30."
    icon: "🚀"

closing_question: "Which of these surprised you most? Share it with someone today."


## SUCCESS-STORY

<!-- What this page does: Prove that someone from here built something extraordinary. -->

person_name: "Full Name"
person_title: "Founder & CEO, Company Name"
person_location: "City, Country"
person_country_flag: "🇺🇬"
person_photo: assets/profile-name.jpg
# If no photo, leave blank — Claude will generate a styled avatar

story_innovation:
  title: "The Innovation"
  body: "2-3 sentences. What did they build? Why does it matter? What problem does it solve? Lead with the human impact, not the technology."

story_journey:
  title: "The Journey"
  body: "2-3 sentences. Where did the idea come from? What obstacles did they face? What made them keep going? Be specific — vague inspiration is forgettable."

achievements:
  - icon: "🏆"
    value: "35000"
    suffix: "$"
    prefix: true
    label: "Prize Money Won"
  - icon: "🌍"
    value: "4"
    suffix: ""
    label: "Countries Served"
  - icon: "👥"
    value: "500000"
    suffix: "+"
    label: "Lives Impacted"
  - icon: "⏱️"
    value: "2"
    suffix: " Min"
    label: "Test Duration"
# Note: values are raw numbers for the count-up animation

signature_quote: "The exact words they said, in quotation marks." — Name, Title

lesson:
  title: "What You Can Learn"
  body: "2-3 sentences distilling the lesson into something the reader can apply today. Not abstract inspiration — a concrete mindset shift."


## MENTAL-HEALTH

<!-- What this page does: Create a moment of permission to breathe. No advice. No pressure. -->

headline: "Take a Breath"
opening: "One line that immediately makes them feel seen. Not advice — acknowledgment."

affirmation: "A full affirmation paragraph. 3-4 sentences. This is not a listicle. Write as if you are speaking directly to one person who is exhausted and needs to hear something true."

breathing_prompt: "Try this: Breathe in for 4 counts. Hold for 4. Out for 6. Do it once — right now."

tip_title: "One Thing for Today"
tip_body: "One practical, gentle action. Not a productivity hack. Something kind."

check_in: "How are you actually doing today? Not the polished answer — the real one."
# Note: This is a reflective question displayed on screen — not a form


## INSPIRATION

<!-- What this page does: The emotional peak. Let the language do the heavy lifting. -->

type: "poem"
# Options: "poem", "quote-series", "short-essay", "letter"

title: "Title of the Piece"
author: "Name — or 'KandaNews Africa' if original"

# For a poem: paste the full poem text, line by line
content: |
  First line of the poem
  Second line continues

  A new stanza begins here
  Each line matters

  The final line should land like a statement.
  Not a whisper. A declaration.

# For a quote-series: provide 3-5 quotes
# quotes:
#   - text: "The quote text."
#     author: "Name, Title"

display_note: "Any note about typographic treatment — e.g. 'highlight the last stanza', 'bold the third line'"


## CAREERS

<!-- What this page does: Make opportunity concrete and reachable. -->

headline: "Opportunities Open Now"
subtitle: "Curated for you — apply before the deadline"

featured_opportunity:
  title: "Job/Programme Title"
  organisation: "Organisation Name"
  location: "City, Country (or Remote)"
  type: "Full-time / Internship / Fellowship / Grant"
  salary: "UGX 2,500,000/month"  # or "Stipend provided" or "Unpaid"
  deadline: "March 31, 2026"
  description: "2 sentences. What is the role? Who is it for? What will they learn or earn?"
  requirements: "2-3 minimum requirements, written as plain language not HR jargon"
  apply_url: "https://..."  # if available

listings:
  - title: "Role Title"
    organisation: "Org Name"
    location: "City"
    type: "Full-time"
    deadline: "April 15, 2026"
    salary: "KES 85,000/month"
    description: "1-2 sentences."

  - title: "Role Title"
    organisation: "Org Name"
    location: "Remote"
    type: "Fellowship"
    deadline: "April 30, 2026"
    salary: "Stipend + accommodation"
    description: "1-2 sentences."

  - title: "Role Title"
    organisation: "Org Name"
    location: "Nairobi"
    type: "Internship"
    deadline: "Rolling"
    salary: "Paid"
    description: "1-2 sentences."


## ENTERTAINMENT

<!-- What this page does: Joy, culture, vibrancy. Let them breathe and enjoy. -->

headline: "What's Hot Right Now"
subtitle: "Entertainment, culture, and what everyone is talking about"

featured:
  type: "music"  # Options: music, film, sport, viral
  title: "Song / Film / Event Title"
  artist_or_team: "Artist or Team Name"
  description: "2-3 sentences. Why is this moment culturally significant? What does it mean for the audience?"
  stat: "500M streams in 3 weeks"

stories:
  - category: "Music"
    headline: "Headline about music story"
    body: "2 sentences."

  - category: "Film & TV"
    headline: "Headline about film or TV"
    body: "2 sentences."

  - category: "Sport"
    headline: "Sport headline"
    body: "2 sentences."


## SONG-OF-AFRICA

<!-- What this page does: Reconnect the reader to African culture and pride. -->

song_title: "Song Title"
artist_name: "Artist Name"
artist_origin: "Country, Region"
genre: "Afrobeats / Amapiano / Afropop / Highlife / etc."
release_year: "2024"

lyrics_excerpt: |
  Paste 4-8 lines of lyrics that best represent the song's spirit.
  Make sure these are the most powerful lines.

story: "2-3 sentences. What is the story behind this song? Why did the artist write it? What cultural moment or personal experience does it capture?"

why_it_matters: "1-2 sentences. Why does this song matter to Africa right now? What does it say about where we are as a generation?"

listen_platform: "Spotify / Apple Music / YouTube"
listen_url: "https://..."


## BOOK-REVIEW

<!-- What this page does: Plant a seed of knowledge they'll carry past the edition. -->

book_title: "Book Title"
author: "Author Name"
published_year: "2023"
genre: "Business / Self-Help / Fiction / History / etc."
rating: 4.5  # out of 5
cover_image: assets/book-cover.jpg

why_read: "1-2 sentences. The compelling reason to pick this up — not a summary, a reason."

key_takeaways:
  - "First key insight from the book — stated as a lesson, not a plot summary."
  - "Second key insight."
  - "Third key insight."

book_quote: "The most powerful line from the book, in quotation marks." — Author Name

closing: "One sentence recommendation. Who should read this and when?"


## COMMUNITY

<!-- What this page does: Show the reader they are not alone. The movement is real. -->

headline: "Your Community is Real"
subtitle: "KandaNews readers across Africa"

community_stat_readers: "47,000"
community_stat_countries: "12"
community_stat_editions: "89"

shoutouts:
  - name: "First Name"
    location: "City, Country"
    flag: "🇺🇬"
    message: "Their message. What they said about KandaNews or what they are working on. Keep it authentic — don't over-edit."

  - name: "First Name"
    location: "City, Country"
    flag: "🇰🇪"
    message: "Their message."

  - name: "First Name"
    location: "City, Country"
    flag: "🇳🇬"
    message: "Their message."

  - name: "First Name"
    location: "City, Country"
    flag: "🇿🇦"
    message: "Their message."

community_cta: "Want to be featured? Share this edition and tag us."


## PODCAST

<!-- What this page does: Show there is more depth available if they want it. -->

episode_number: "EP. 024"
episode_title: "Episode Title"
episode_duration: "42 mins"
release_date: "March 14, 2026"

guest_name: "Guest Full Name"
guest_title: "Title, Organisation"
guest_photo: assets/podcast-guest.jpg

episode_description: "2 sentences. What is this episode about? What question does it answer?"

key_topics:
  - "Topic or discussion point one"
  - "Topic or discussion point two"
  - "Topic or discussion point three"

best_moment: "The most quotable line from the episode, in quotation marks." — Guest Name

listen_url: "https://..."
listen_platforms: "Spotify · Apple Podcasts · YouTube"


## NEXT-EDITION

<!-- What this page does: Leave them wanting more. Curiosity over information. -->

next_date: "March 21, 2026"
next_edition_label: "Next Week"

teasers:
  - "The business that survived what others could not — we tell the full story."
  - "One question. Four African leaders. Completely different answers."
  - "The skill that pays more than a degree in 2026 — and how to start learning it today."
# Keep teasers intriguing but vague. Don't give away the answer.

cta: "Set your reminder. You will not want to miss this."


## BACK-COVER

<!-- What this page does: Close powerfully. End on movement, not sentiment. -->

closing_headline: "The Work Doesn't End Here"
closing_message: "3-4 sentences. A warm but energising close. Remind them what they now know. Remind them what they can do. Do not beg. Do not thank excessively. End as equals."

action_prompts:
  - icon: "📤"
    action: "Share this edition with one person who needs it today."
  - icon: "💼"
    action: "Apply to at least one opportunity from the Careers page."
  - icon: "📲"
    action: "Follow us @KandaNewsAfrica for daily updates."

final_line: "The world is waiting for what only you can do."
# This is the last line of the entire edition. Make it count.

social_handles:
  instagram: "@KandaNewsAfrica"
  twitter: "@KandaNews"
  whatsapp: "Join our WhatsApp community"
