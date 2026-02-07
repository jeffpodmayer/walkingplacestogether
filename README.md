# Walking Places Together - Database Sync Tracker

This document tracks database changes made locally that need to be replicated on the live WordPress site.

---

## Current Tasks to Sync to Live Site

### 1. Rework Trail Stats in ACF

- [ ] **Update ACF field structure for trail statistics**
  - Review and reorganize the trail stats fields (distance, trip_duration_days, direction_style, season, region)
  - Ensure field types and settings match local configuration
  - Export updated field group as JSON
  - Import on live site via ACF > Tools > Import

### 1. Added Galerie ACF Fields

- Download Pliug in
- Add ACF fields for trail_overview_images, trail_photo_gallery

---

## Sync Instructions

### Step 1: Backup Live Database

Always backup the live database before making changes.

### Step 2: Export ACF Field Groups (Local)

1. Go to ACF > Tools > Export Field Groups
2. Select the Trail field group
3. Export as JSON or PHP code

### Step 3: Install Plugin (Live Site)

1. Log into live site wp-admin
2. Go to Plugins > Add New
3. Search for "Blocks for ACF"
4. Install and activate

### Step 4: Import ACF Fields (Live Site)

1. Go to ACF > Tools > Import
2. Import the exported field group JSON
3. Verify all fields appear correctly

### Step 5: Test

1. View a trail post on live site
2. Confirm all trail stats display correctly
3. Test ACF blocks functionality

---

## Notes

- Local development URL: `http://localhost:8888`
- Keep this README updated as new sync tasks arise
- Document any issues encountered during sync process below

---

## Change Log

### 2025-02-05 - Trail Stats & ACF Blocks

- Reworking trail statistics field structure in ACF
- Adding Blocks for ACF plugin for enhanced functionality
