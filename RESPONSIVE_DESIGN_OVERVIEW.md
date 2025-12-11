# Tổng Quan Responsive Design - Xanh Organic

## 📐 Breakpoints Hiện Tại

Dự án sử dụng 3 breakpoint chính (xem trong `css/breakpoints.css`):

```
Mobile:  < 768px          (iPhone, Small Phones)
Tablet:  768px - 1024px   (iPad, Tablet Devices)
Desktop: >= 1025px        (Desktop, Large Screens)
```

### CSS Variables (trong `css/breakpoints.css`)
```css
--breakpoint-mobile-max: 767px;
--breakpoint-tablet-min: 768px;
--breakpoint-tablet-max: 1024px;
--breakpoint-desktop-min: 1025px;
```

---

## 🎨 Color System (Primary Colors)

File: `css/styles.css` - CSS Variables

### Light Mode (Default)
```css
--primary: #b6e633;           /* Xanh lá chính (Tailwind) */
--primary-dark: #9acc2a;      /* Xanh lá đậm */
--background-light: #FBFBF7;  /* Nền sáng */
--text-light: #161811;        /* Chữ tối */
--card-light: #ffffff;        /* Card trắng */
--border-light: #e3e5dc;      /* Đường viền nhạt */
--muted-light: #7e8863;       /* Chữ mềm */
```

### Dark Mode (Defined nhưng chưa dùng)
```css
--background-dark: #1d2111;
--text-dark: #f7f8f6;
--card-dark: #2a2e1e;
--border-dark: #3c4031;
--muted-dark: #a1a893;
```

### Semantic Colors
```css
--danger: #ef4444;      /* Đỏ - Xóa, lỗi */
--success: #22c55e;     /* Xanh lá - Thành công */
```

---

## 📱 Header Layout - Responsive

File: `includes/header.php` (lines 69-330+)

### **Desktop & Tablet (≥ 768px)**
- **Layout**: Một hàng duy nhất (flexbox)
- **Cấu trúc**: `Logo Section` | `Navigation` | `Header Actions`
  - Logo + Brand Name
  - Navigation Menu (Product, About, Contact, etc.)
  - Search Box + User Icons (Profile, Cart, More)
- **Grid**: `display: flex; justify-content: space-between;`
- **Height**: 80px

### **Mobile (< 768px)**
- **Layout**: 2 hàng (2-row layout)
  
**Row 1 (Menu - Logo - Cart)**:
  - Grid: `grid-template-columns: 1fr 2fr 1fr;` (3 cột)
  - Left: Menu Button + User Icon
  - Center: Logo (centered, max-height 50px)
  - Right: Cart Icon + Wishlist (nếu có)
  - Height: 70px

**Row 2 (Search Bar)**:
  - Full width search bar
  - Border: Top & Bottom
  - Background: #f9f9f9
  - Input width: 100% - 2rem (margin)

---

## 🏠 Product Grid - Responsive

File: `css/styles.css` (lines 160-190)

### Grid Columns
```css
Mobile:  grid-template-columns: repeat(1, 1fr)  /* 1 cột */
Tablet:  @media (min-width: 640px)   → repeat(2, 1fr)  /* 2 cột */
Desktop: @media (min-width: 1024px)  → repeat(3, 1fr)  /* 3 cột */
```

### Gap & Padding
```css
Gap:     1.5rem
Mobile:  padding: 0.75rem (reduced)
Desktop: padding: 1rem
```

### Product Card Responsive
```
Mobile:
  - Product Image: aspect-ratio: 1 (square)
  - Font Size: 0.875rem (reduced)
  - Padding: 0.75rem
  - Button: 0.5rem 0.75rem padding

Desktop:
  - Product Image: aspect-ratio: 1
  - Font Size: 1rem (normal)
  - Padding: 1rem
  - Button: 0.625rem 1rem padding
```

---

## 👥 Footer - Social Networks Grid

File: `css/styles.css` (lines 405-480)

### Grid Layout
```css
Mobile:  grid-template-columns: repeat(1, 1fr)  /* 1 cột */
Tablet:  @media (min-width: 640px)   → repeat(2, 1fr)  /* 2 cột */
Desktop: @media (min-width: 1024px)  → repeat(4, 1fr)  /* 4 cột */
```

### Social Card Responsive
```
Mobile:
  - Padding: 1.25rem
  - Icon Size: 50px
  - Font Size: 0.8rem

Desktop:
  - Padding: 1.5rem
  - Icon Size: 60px
  - Font Size: 1rem
```

---

## 📋 Footer Grid - General Layout

File: `css/styles.css` (lines 500-540)

### Footer Layout
```css
Mobile:  grid-template-columns: repeat(1, 1fr)  /* 1 cột */
Desktop: @media (min-width: 768px) → repeat(4, 1fr)  /* 4 cột */
```

- **Max Width**: 1280px (container)
- **Padding**: 3rem 1rem
- **Top Margin**: 4rem

---

## 🔧 Admin-Specific Styles

File: `css/admin-mobile.css`

### User Card (Mobile-Optimized)
```
Layout: Vertical (flex-direction: column)
Card Header:
  - Avatar: 65px × 65px (circular)
  - Name: Center-aligned
  - ID/Email: Smaller font

Card Body:
  - Info rows: Vertical (label trên, value dưới)
  - Padding: 0.85rem 1rem per row
  - Border-bottom: 1px solid #f1f3f5

Action Buttons:
  - Container: display: flex (2-3 buttons side-by-side)
  - Min Height: 42px
  - Font Size: 0.85rem
```

### Badge Styles (Admin Products Table)
```css
Badge Variants:
  - Featured: #fef08a (yellow) on #b45309 text
  - New: #dbeafe (blue) on #2563eb text
  - Organic: #bbf7d0 (green) on #166534 text
  - Out of Stock: #fee2e2 (red) on #b91c1c text
```

---

## 🎯 Typography - Font Sizing

### Default Font
```css
font-family: 'Be Vietnam Pro', sans-serif;
line-height: 1.6;
```

### Responsive Font Sizes
```
Mobile:
  - Title: 1.25rem
  - Heading: 1rem
  - Body: 0.875rem
  - Small: 0.8rem - 0.75rem

Desktop:
  - Title: 1.5rem
  - Heading: 1.125rem
  - Body: 1rem
  - Small: 0.875rem
```

---

## 🛠️ Utility Classes

File: `css/breakpoints.css` (lines 35-73)

### Show/Hide on Different Devices
```css
.hide-mobile:        display: none < 768px, block ≥ 768px
.show-mobile:        display: none ≥ 768px, block < 768px
.hide-desktop:       display: none ≥ 1025px
.show-desktop:       display: none < 1025px
```

---

## 📐 Layout Container

File: `css/styles.css` (lines 580-600)

### Main Layout
```css
.container:
  - max-width: 1280px
  - margin: 0 auto
  - padding: 0 1rem

.main-layout:
  - Mobile:  grid-template-columns: 1fr (full width)
  - Desktop: grid-template-columns: 280px 1fr (sidebar + content)
  - Gap: 2rem
  - Padding: 2rem 1rem
```

---

## ✅ Responsive Breakpoints Summary

### Media Query Patterns Used
```css
/* Mobile First */
@media (max-width: 767px)          /* Only mobile */
@media (min-width: 768px)          /* Tablet and up */
@media (min-width: 640px)          /* Tablet and up (intermediate) */
@media (min-width: 1024px)         /* Desktop and up */
@media (min-width: 1025px)         /* Desktop only (strict) */

/* Mobile Specific */
@media (max-width: 640px)          /* Small mobile */

/* Range */
@media (min-width: 768px) and (max-width: 1024px)  /* Tablet only */
```

---

## 🎨 Current Issues & Notes

1. **Tailwind vs Custom CSS Overlap**
   - Header uses inline styles + custom CSS
   - Some inconsistency between Tailwind classes and custom CSS
   - Suggestion: Migrate fully to Tailwind or consolidate custom CSS

2. **Mobile Search Positioning**
   - Uses `width: 100vw; margin-left: calc(-50vw + 50%);`
   - This is to stretch full width on mobile
   - Could be problematic on different viewport sizes

3. **Admin Mobile CSS**
   - Uses many `!important` flags
   - Suggest reviewing specificity to reduce !important usage

4. **No Dark Mode CSS**
   - Dark mode variables defined but never used in media queries
   - Requires `@media (prefers-color-scheme: dark)` to activate

---

## 📊 Tailwind Configuration

File: `tailwind.config.js`

### Theme Extensions
```javascript
colors: {
  primary: "#b6e633",
  primary-dark: "#9acc2a",
  background-light: "#f7f8f6",
  text-light: "#161811",
  card-light: "#ffffff",
  border-light: "#e3e5dc",
  muted-light: "#7e8863",
}

fontFamily: {
  display: ["Be Vietnam Pro", "sans-serif"]
}
```

### Content Scanning
- Scans all `.php` files in root and subdirectories
- Scans `admin/**/*.php`
- Scans `includes/**/*.php`

---

## 🔄 Responsive Flow Summary

```
┌─────────────────────────────────────────┐
│         All Screen Sizes                 │
├─────────────────────────────────────────┤
│ Font: Be Vietnam Pro                    │
│ Colors: Green (#b6e633) Primary         │
│ Layout: CSS Grid & Flexbox              │
└─────────────────────────────────────────┘
        ↓
┌─────────────────────────────────────────┐
│  Mobile < 768px                         │
├─────────────────────────────────────────┤
│ Header: 2 rows (Menu-Logo-Cart, Search) │
│ Products: 1 column grid                 │
│ Footer: 1 column                        │
│ Font: Reduced sizes (0.75-0.875rem)     │
│ Padding: Compact (0.75-1rem)            │
└─────────────────────────────────────────┘
        ↓
┌─────────────────────────────────────────┐
│  Tablet 768px - 1024px                  │
├─────────────────────────────────────────┤
│ Header: Single row (desktop style)      │
│ Products: 2 column grid                 │
│ Footer: 2 columns (social), 4 (general) │
│ Font: Medium sizes                      │
│ Padding: Normal (1-1.5rem)              │
└─────────────────────────────────────────┘
        ↓
┌─────────────────────────────────────────┐
│  Desktop >= 1025px                      │
├─────────────────────────────────────────┤
│ Header: Single row, full layout         │
│ Products: 3 column grid                 │
│ Footer: 4 columns                       │
│ Sidebar: Visible (280px)                │
│ Font: Full sizes (1-1.5rem)             │
│ Padding: Generous (1.5-2rem)            │
└─────────────────────────────────────────┘
```

---

Generated: 2025-12-11
