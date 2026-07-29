/**
 * Website Analysis API
 * Uses Supabase Edge Function for AI-powered website analysis
 */

import { supabase } from "@/integrations/supabase/client";

export interface WebsiteAnalysisConfig {
  app_name?: string;
  appName?: string;
  primary_color?: string;
  primaryColor?: string;
  accent_color?: string;
  accentColor?: string;
  navigation_style?: "tabs" | "drawer" | "bottom-nav" | "bottom_tabs" | "top_tabs";
  navigationStyle?: string;
  features?: string[];
  app_category?: string;
  appCategory?: string;
  description?: string;
  icon_style?: string;
  iconStyle?: string;
  splash_screen_style?: string;
}

export interface AnalysisResponse {
  data: { config: WebsiteAnalysisConfig } | null;
  error: Error | null;
}

/**
 * Normalize config field names from various response formats
 */
function normalizeConfig(data: Record<string, unknown>): WebsiteAnalysisConfig {
  // Handle nested config object if present
  const raw = (data.config as Record<string, unknown>) || data;
  return {
    app_name: (raw.appName || raw.app_name) as string | undefined,
    primary_color: (raw.primaryColor || raw.primary_color || "#3B82F6") as string,
    accent_color: (raw.accentColor || raw.accent_color || "#10B981") as string,
    navigation_style: (raw.navigationStyle || raw.navigation_style || "bottom_tabs") as WebsiteAnalysisConfig["navigation_style"],
    features: (raw.features || []) as string[],
    app_category: (raw.appCategory || raw.app_category || "utilities") as string,
    description: (raw.description || "") as string,
    icon_style: (raw.iconStyle || raw.icon_style || "rounded") as string,
    splash_screen_style: (raw.splash_screen_style || "gradient") as string,
  };
}

/**
 * Analyze a website using Supabase Edge Function
 */
export async function analyzeWebsite(websiteUrl: string): Promise<AnalysisResponse> {
  try {
    console.log("[analyzeWebsite] Using Supabase Edge Function");
    
    let data: any = null;
    let error: any = null;

    try {
      const result = await supabase.functions.invoke("analyze-website", {
        body: { websiteUrl },
      });
      data = result.data;
      error = result.error;
    } catch (invokeError: any) {
      // supabase SDK may throw on non-2xx responses
      console.error("[analyzeWebsite] Invoke threw:", invokeError);
      const msg = invokeError?.message || "";
      if (msg.includes("402") || msg.toLowerCase().includes("credit")) {
        return {
          data: null,
          error: new Error("AI credits exhausted. Please add credits to your workspace to continue."),
        };
      }
      if (msg.includes("429") || msg.toLowerCase().includes("rate limit")) {
        return {
          data: null,
          error: new Error("Rate limit exceeded. Please try again in a moment."),
        };
      }
      return {
        data: null,
        error: new Error(msg || "AI analysis failed"),
      };
    }

    // Edge function returns error JSON in data for non-2xx responses
    if (data?.error) {
      console.error("[analyzeWebsite] API error:", data.error);
      return {
        data: null,
        error: new Error(data.error),
      };
    }

    if (error) {
      console.error("[analyzeWebsite] Edge function error:", error);
      const msg = error.message || "";
      if (msg.includes("402")) {
        return {
          data: null,
          error: new Error("AI credits exhausted. Please add credits to your workspace to continue."),
        };
      }
      if (msg.includes("429")) {
        return {
          data: null,
          error: new Error("Rate limit exceeded. Please try again in a moment."),
        };
      }
      return {
        data: null,
        error: new Error(msg || "AI analysis failed"),
      };
    }

    if (!data) {
      return {
        data: null,
        error: new Error("No response from AI service"),
      };
    }

    return { data: { config: normalizeConfig(data) }, error: null };
  } catch (error) {
    console.error("[analyzeWebsite] Error:", error);
    return {
      data: null,
      error: error instanceof Error ? error : new Error("Analysis failed"),
    };
  }
}
