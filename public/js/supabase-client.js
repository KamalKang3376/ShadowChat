import { createClient } from
    "https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2/+esm";

const SUPABASE_URL =
    "https://dfgsjwmfxddyeejoemck.supabase.co";

const SUPABASE_PUBLISHABLE_KEY =
    "sb_publishable_9QyWMb4f0ST1Cgy0uNAy0g_hLqLBsUA";

export const supabase = createClient(
    SUPABASE_URL,
    SUPABASE_PUBLISHABLE_KEY
);