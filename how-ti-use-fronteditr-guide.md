# Dynamic Content Pro - User Guide

## What is Dynamic Content Pro?

Display different content to different users based on **conditions** like:
- User role (admin, subscriber, etc.)
- Device (mobile, desktop, tablet)
- Location (country, city)
- Time/Date
- WooCommerce cart status
- And much more!

---

## Quick Start (3 Steps)

### 1. Create Dynamic Content

1. Go to **Dynamic Content → Add New**
2. Enter a title (for your reference only)
3. The React interface will load automatically

### 2. Add Conditions & Content

**Add a Variant:**
- Click "**+ Add Condition**" under "Content Variant #1"
- Choose condition type (e.g., "User Role")
- Select operator (equals, not equals, etc.)
- Enter value (e.g., "administrator")
- Enter content to show when condition matches

**Add Default Content:**
- Scroll down to "Default Content" section
- Enter content to show when NO conditions match

### 3. Use the Shortcode

- Save your content
- Copy the shortcode from the right sidebar
- Paste it anywhere: posts, pages, widgets

```
[dcp_content id="123"]
```

Done! 

---

## Detailed Walkthrough

### Creating Your First Dynamic Content

**Example: Show different messages to logged-in vs logged-out users**

#### Step 1: Create New Content
```
WordPress Admin → Dynamic Content → Add New
Title: "Welcome Message"
```

#### Step 2: Set Up Conditions
```
Content Variant #1:
├─ Condition 1:
│  ├─ Type: Login Status
│  ├─ Operator: Equals
│  └─ Value: Logged In
└─ Content: "Welcome back, valued member!"

Default Content:
└─ Content: "Join us today and get exclusive access!"
```

#### Step 3: Insert Shortcode
```
Copy: [dcp_content id="123"]
Paste in any post/page
```

**Result:**
- Logged-in users see: "Welcome back, valued member!"
- Logged-out users see: "Join us today and get exclusive access!"

---

## Using with Page Builders

### Elementor Integration

1. **Open page with Elementor**
2. **Search for widget:** "Dynamic Content Pro"
3. **Drag to page**
4. **Configure options:**
   - Content Source: Choose "Saved Dynamic Content" or "Custom"
   - Select your saved content OR build inline
   - Style as needed
5. **Preview & Publish**

### Gutenberg (Block Editor)

1. Add a **Shortcode Block**
2. Paste: `[dcp_content id="123"]`
3. Publish

### Classic Editor

1. Switch to **Text mode**
2. Paste: `[dcp_content id="123"]`
3. Publish

---

## Condition Types Explained

### User Conditions

#### User Role
```
Show content only to specific roles
Example: Show pricing table to "Administrator" only
```

#### Login Status
```
Options:
- Logged In: User is logged into WordPress
- Logged Out: User is a visitor/guest
```

#### User Meta
```
Advanced: Target users by custom meta fields
Example: Show content to users with "premium_member" = true
```

### Location Conditions

#### Country
```
Target by country code
Example: Show "Free shipping" to US users
Value: US, UK, CA (comma-separated)
```

#### City
```
Target specific cities
Example: Show local event to "New York" residents
```

#### IP Address
```
Target specific IP addresses
Example: Show internal content to office IPs
```

### Device Conditions

#### Device Type
```
Options:
- Desktop: Show on computers
- Mobile: Show on phones
- Tablet: Show on tablets

Example: Show "Download App" button only on mobile
```

#### Browser
```
Target specific browsers
Example: Show "Use Chrome for best experience" to Firefox users
```

### Time Conditions

#### Date Range
```
Show content between specific dates
Example: Holiday sale from Dec 20 - Dec 31

Start Date: 2024-12-20
End Date: 2024-12-31
Content: "Holiday Sale - 50% OFF!"
```

#### Day of Week
```
Show content on specific days
Example: "Weekend Special" on Saturday & Sunday
```

#### Time Range
```
Show content during specific hours
Example: "Support online" from 9 AM - 5 PM
```

### Page Conditions

#### Page Type
```
Options:
- Home: Front page only
- Single: Single post pages
- Page: Static pages
- Archive: Category/tag archives
- Search: Search results page

Example: Show newsletter signup on blog posts only
```

#### URL Parameter
```
Check for URL query parameters
Example: Show discount if URL has ?promo=summer

Parameter: promo
Value: summer
URL: yoursite.com/page?promo=summer
```

#### Referrer
```
Show content based on where user came from
Example: Thank visitors from Facebook
```

### WooCommerce Conditions

#### Cart Total
```
Operators:
- Greater Than: Cart total > $50
- Less Than: Cart total < $20
- Equals: Cart total = $100

Example: "Free shipping" when cart > $50
```

#### Cart Items
```
Check if specific products are in cart
Example: Show accessory when camera is in cart
```

#### Purchased Product
```
Show content to users who bought specific products
Example: Upsell to previous customers
```

### Advanced Conditions

#### Cookie
```
Check browser cookie values
Example: Show returning visitor message

Name: returning_visitor
Operator: Exists
```

#### Session Variable
```
Check PHP session data
Example: Multi-step form progress
```

#### Custom PHP Code
```
Advanced users only
Write custom PHP to evaluate conditions

Example:
return current_user_can('edit_posts');
```

#### A/B Test
```
Randomly split traffic 50/50
Perfect for testing which content converts better

Test ID: homepage_hero
Variant A: Image with CTA
Variant B: Video with CTA
```

---

## Condition Logic

### AND Logic (Default)
```
ALL conditions must match

Example:
✓ User Role = Administrator
✓ Device = Desktop
Result: Show only to admins on desktop
```

### OR Logic
```
ANY condition can match

Example:
✓ User Role = Administrator
✓ User Role = Editor
Result: Show to admins OR editors
```

**Change logic:**
```
Top of builder → "Condition Logic" dropdown → Select AND/OR
```

---

## Real-World Examples

### Example 1: Mobile-Only Download Button
```
Condition: Device Type = Mobile
Content: <button>Download Our App</button>
Default: <!-- Empty -->
```

### Example 2: Location-Based Pricing
```
Variant 1:
├─ Condition: Country = US
└─ Content: "Starting at $99 USD"

Variant 2:
├─ Condition: Country = UK
└─ Content: "Starting at £79 GBP"

Default: "Starting at $99"
```

### Example 3: Flash Sale Timer
```
Condition: Date Range
├─ Start: 2024-12-01
├─ End: 2024-12-03
└─ Content: "48 Hour Flash Sale - Ends Soon!"

Default: "Regular prices"
```

### Example 4: Cart Upsell
```
Condition: Cart Total > $50
Operator: Greater Than
Value: 50
Content: "You're $20 away from FREE shipping!"

Default: <!-- Empty -->
```

### Example 5: Subscriber Exclusive
```
Variant 1:
├─ Condition: User Role = Subscriber
└─ Content: "Download your free eBook here: [link]"

Default: "Subscribe to access exclusive content"
```

---

## Shortcode Options

### Basic Usage
```
[dcp_content id="123"]
```

### Inline Conditions (No need to create content first)

#### Show Content
```
[dcp_show role="administrator"]
  This content only shows to admins
[/dcp_show]
```

#### Hide Content
```
[dcp_hide device="mobile"]
  This content is hidden on mobile
[/dcp_hide]
```

### Multiple Conditions
```
[dcp_show role="administrator,editor" operator="OR"]
  Shows to admins OR editors
[/dcp_show]
```

### All Shortcode Parameters
```
role="administrator"
logged_in="logged_in" or "logged_out"
country="US,UK,CA"
device="desktop" or "mobile" or "tablet"
date_from="2024-12-01"
date_to="2024-12-31"
page_type="home" or "single" or "page"
url_param="promo"
url_value="summer"
operator="AND" or "OR"
```

---

## Styling Your Content

### Use HTML & CSS
```
Content field supports HTML:

<div style="background: #f0f0f0; padding: 20px;">
  <h2>Special Offer</h2>
  <p>Limited time only!</p>
</div>
```

### Use WordPress Shortcodes
```
You can nest shortcodes:

[dcp_show role="subscriber"]
  [contact-form-7 id="123"]
[/dcp_show]
```

### Add Custom Classes
```
<div class="my-dynamic-content">
  Your content here
</div>

Then style in your theme's CSS:
.my-dynamic-content { color: red; }
```

---

## Analytics & Tracking

### View Statistics
```
WordPress Admin → Dynamic Content → All Items
Check the "Views" column to see impression counts
```

### Track Conversions
```
Enable analytics:
Dynamic Content → Settings → Enable Analytics ✓

Each variant tracks how many times it was displayed
```

---

## Settings

### Access Settings
```
Dynamic Content → Settings
```

### Available Options

**Enable Caching**
- Speeds up content delivery
- Recommended: ON
- Cache duration: 3600 seconds (1 hour)

**Enable Analytics**
- Track which variants are shown
- See impression counts
- Recommended: ON for A/B testing

**Clear Cache**
- Use after updating conditions
- Click "Clear Cache Now" button

---

## Troubleshooting

### Content Not Showing

**Check 1: Is condition met?**
```
Test condition manually:
- Log out/in to test login status
- Use mobile emulator to test device
- Check date/time for time-based conditions
```

**Check 2: Is shortcode correct?**
```
Correct: [dcp_content id="123"]
Wrong: [dcp_content id=123] (missing quotes)
```

**Check 3: Clear cache**
```
Dynamic Content → Settings → Clear Cache Now
```

### React Interface Not Loading

**Solution:**
```
1. Hard refresh browser: Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)
2. Check browser console for errors (F12)
3. Verify build/index.js exists in plugin folder
```

### Conditions Not Working

**Debug checklist:**
```
✓ Condition type is correct
✓ Operator makes sense (equals vs contains)
✓ Value is properly formatted
✓ Condition logic is AND/OR appropriate
✓ Cache is cleared
```

### Elementor Widget Missing

**Solution:**
```
1. Ensure Elementor is installed & active
2. Refresh Elementor: Settings → Advanced → Regenerate Files
3. Check WordPress Admin → Plugins → Dynamic Content Pro is active
```

---

## Pro Tips

### Tip 1: Test Before Publishing
```
Create content with test conditions
Preview on incognito/different device
Adjust as needed
```

### Tip 2: Use Descriptive Titles
```
Good: "Mobile CTA - Homepage"
Bad: "Dynamic Content 1"
```

### Tip 3: Keep It Simple
```
Don't overcomplicate conditions
Start with 1-2 conditions per variant
Add more only if needed
```

### Tip 4: Combine with Other Plugins
```
Works great with:
- Contact Form 7
- WooCommerce
- Membership plugins
- Any shortcode-based plugin
```

### Tip 5: Mobile-First Approach
```
Always test on mobile devices
50%+ of traffic is mobile
Use device conditions wisely
```

---

## Need Help?

### Documentation
- Check inline tooltips in the builder
- Hover over "?" icons for help

### Common Questions

**Q: Can I use multiple conditions?**
A: Yes! Click "+ Add Condition" as many times as needed.

**Q: Can I nest shortcodes?**
A: Yes! Dynamic Content Pro supports nested shortcodes.

**Q: Does it work with WooCommerce?**
A: Yes! Full WooCommerce integration included.

**Q: Is it compatible with my theme?**
A: Yes! Works with any WordPress theme.

**Q: Can I export/import content?**
A: Use WordPress's built-in export/import for the post type "dcp_content".

---

## Learning Resources

### Video Tutorials (Suggested)
1. Getting Started (5 min)
2. User Conditions (10 min)
3. WooCommerce Integration (8 min)
4. A/B Testing Setup (7 min)

### Example Use Cases
- Membership content restriction
- Location-based pricing
- Mobile app promotion
- Time-sensitive offers
- Cart abandonment messages
- Role-based documentation

---

**Happy Building! **

*Dynamic Content Pro - Show the right content to the right people at the right time.*