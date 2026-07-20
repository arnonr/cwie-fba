# Design: Remove Student Cancel Registration Button

This document outlines the changes to hide/comment out the "Cancel Registration" (ยกเลิกการสมัคร) button from the student views on the frontend.

## Proposed Changes

### Frontend Components

#### [MODIFY] [StudentAction.vue](file:///Users/macbook-arnon/Projects/cwie-fba/resources/js/components/student-view/StudentAction.vue)
- Comment out the "Cancel Registration" button using HTML comments `<!-- ... -->` to prevent students from canceling their registration from the student panel.

#### [MODIFY] [index.vue](file:///Users/macbook-arnon/Projects/cwie-fba/resources/js/pages/student2/cwie-data/index.vue)
- Comment out the "Cancel Registration" button using HTML comments `<!-- ... -->` in the secondary student page.

## Verification Plan

### Manual Verification
- Log in as a student, navigate to the Co-op Application page (`student/cwie-data`), and verify that the "Cancel Registration" (ยกเลิกการสมัคร) button is no longer visible.
- Log in as a staff/officer, navigate to the student list/detail pages (`staff/students/view/[id]`), and verify that the "Cancel Registration" (ยกเลิกการสมัคร) button is still visible and functional.
