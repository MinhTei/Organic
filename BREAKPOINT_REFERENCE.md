# 📐 BREAKPOINT REFERENCE CARD

## Breakpoint Chuẩn Toàn Project

```
┌─────────────────┬───────────────────┬──────────────────┐
│  MOBILE         │  TABLET           │  DESKTOP         │
├─────────────────┼───────────────────┼──────────────────┤
│ Max: 767px      │ 768px - 1024px    │ Min: 1025px      │
│                 │                   │                  │
│ iPhone          │ iPad, Galaxy Tab  │ Laptop, Monitor  │
│ Small Android   │ Small Tablets     │ Large Screens    │
└─────────────────┴───────────────────┴──────────────────┘
```

## Media Query Copy-Paste Templates

### Template 1: Mobile Only
```css
@media (max-width: 767px) {
    /* Mobile CSS */
}
```

### Template 2: Tablet Only
```css
@media (min-width: 768px) and (max-width: 1024px) {
    /* Tablet CSS */
}
```

### Template 3: Desktop Only
```css
@media (min-width: 1025px) {
    /* Desktop CSS */
}
```

### Template 4: Mobile + Tablet (Not Desktop)
```css
@media (max-width: 1024px) {
    /* Mobile & Tablet CSS */
}
```

### Template 5: Tablet + Desktop (Not Mobile)
```css
@media (min-width: 768px) {
    /* Tablet & Desktop CSS */
}
```

## Grid Sizing Cheat Sheet

### Product Cards
```
Mobile:  minmax(clamp(160px, 40vw, 280px), 1fr)  [1-2 items]
Tablet:  minmax(200px, 1fr)                       [2-3 items]
Desktop: minmax(240px, 1fr)                       [3-4 items]
```

### Featured Products
```
Mobile:  minmax(clamp(180px, 40vw, 280px), 1fr)  [1-2 items]
Tablet:  minmax(200px, 1fr)                       [2-3 items]
Desktop: minmax(280px, 1fr)                       [3-4 items]
```

### News/Blog Cards
```
Mobile:  minmax(clamp(200px, 40vw, 240px), 1fr)  [1 item]
Tablet:  minmax(220px, 1fr)                       [2 items]
Desktop: minmax(240px, 1fr)                       [2-3 items]
```

## Common Device Sizes

### iPhones
- iPhone SE: 375px
- iPhone 12/13: 390px
- iPhone 14 Pro: 393px
- iPhone 14 Pro Max: 430px

### Android Phones
- Galaxy S21: 360px
- Galaxy S22: 360px
- Pixel 7: 412px

### Tablets
- iPad Mini: 768px
- iPad: 810px (landscape), 810px (portrait)
- iPad Pro 11": 834px

### Laptops/Desktop
- MacBook Air: 1440px
- Full HD Monitor: 1920px
- 2K Monitor: 2560px

## Kiểm tra Responsive trong DevTools

1. Mở DevTools: `F12`
2. Toggle Device: `Ctrl+Shift+M` (hoặc `Cmd+Shift+M`)
3. Chọn device hoặc custom size
4. Test tại các điểm breakpoint:
   - 375px (mobile)
   - 768px (tablet start)
   - 1024px (tablet end)
   - 1025px (desktop start)
   - 1440px (desktop standard)

## File Áp dụng Breakpoint

| File | Mobile | Tablet | Desktop |
|------|--------|--------|---------|
| header.php | ✅ | ✅ | ✅ |
| index.php | ✅ | ✅ | ✅ |
| order_history.php | ✅ | ✅ | ✅ |
| order_detail.php | ✅ | ✅ | ✅ |
| css/breakpoints.css | ✅ | ✅ | ✅ |

## ⚡ Performance Tips

1. **Dùng clamp() thay vì nhiều media query**
   ```css
   /* Tốt */
   font-size: clamp(0.875rem, 2vw, 1.125rem);
   
   /* Kém */
   font-size: 14px;
   @media (min-width: 768px) { font-size: 16px; }
   @media (min-width: 1024px) { font-size: 18px; }
   ```

2. **Mobile-first approach**
   - Viết CSS cho mobile trước
   - Dùng media query để override cho larger screens

3. **Tránh hardcoded pixel values**
   - Dùng relative units (%, vw, clamp)
   - Dùng gap/margin/padding scalable

---

**Last Updated:** 6 Dec 2025
