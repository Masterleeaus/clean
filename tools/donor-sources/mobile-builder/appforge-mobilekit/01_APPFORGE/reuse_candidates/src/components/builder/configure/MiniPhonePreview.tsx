import { useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { X, Maximize2, Minimize2, Home, Search, User, Bell, Settings } from "lucide-react";
import type { AppConfig, NavItem } from "@/stores/useAppStore";
import { cn } from "@/lib/utils";
import { getIconComponent } from "./NavItemEditor";

interface MiniPhonePreviewProps {
  config: AppConfig;
  onExpand?: () => void;
}

const MiniPhonePreview = ({ config, onExpand }: MiniPhonePreviewProps) => {
  const [isVisible, setIsVisible] = useState(true);
  const [isExpanded, setIsExpanded] = useState(false);

  const getDefaultNavItems = (): NavItem[] => {
    const category = config.appCategory?.toLowerCase() || "";
    
    if (category.includes("shop") || category.includes("ecommerce") || category.includes("store")) {
      return [
        { icon: "Home", label: "Home" },
        { icon: "Search", label: "Search" },
        { icon: "Heart", label: "Wishlist" },
        { icon: "ShoppingBag", label: "Cart" },
        { icon: "User", label: "Account" },
      ];
    }
    
    if (category.includes("social") || category.includes("chat") || category.includes("community")) {
      return [
        { icon: "Home", label: "Feed" },
        { icon: "Search", label: "Discover" },
        { icon: "MessageCircle", label: "Messages" },
        { icon: "Bell", label: "Alerts" },
        { icon: "User", label: "Profile" },
      ];
    }
    
    if (category.includes("news") || category.includes("blog") || category.includes("content")) {
      return [
        { icon: "Home", label: "Home" },
        { icon: "Search", label: "Search" },
        { icon: "Heart", label: "Saved" },
        { icon: "Bell", label: "Updates" },
        { icon: "User", label: "Profile" },
      ];
    }
    
    return [
      { icon: "Home", label: "Home" },
      { icon: "Search", label: "Search" },
      { icon: "Bell", label: "Alerts" },
      { icon: "Settings", label: "Settings" },
      { icon: "User", label: "Profile" },
    ];
  };

  // Use custom items if defined, otherwise use defaults
  const navItems = config.customNavItems || getDefaultNavItems();

  const renderAppIcon = () => {
    if (config.customIcon) {
      return (
        <img 
          src={config.customIcon} 
          alt="App icon" 
          className="w-full h-full object-cover"
        />
      );
    }

    const baseClass = "w-full h-full flex items-center justify-center";
    
    switch (config.iconStyle) {
      case "modern":
      case "gradient":
        return (
          <div 
            className={baseClass}
            style={{ background: `linear-gradient(135deg, ${config.primaryColor}, ${config.accentColor})` }}
          >
            <span className="text-white text-[10px] font-bold">
              {(config.appName || "A")[0].toUpperCase()}
            </span>
          </div>
        );
      case "outlined":
        return (
          <div 
            className={`${baseClass} border`}
            style={{ borderColor: config.primaryColor, backgroundColor: "white" }}
          >
            <span className="text-[10px] font-bold" style={{ color: config.primaryColor }}>
              {(config.appName || "A")[0].toUpperCase()}
            </span>
          </div>
        );
      default:
        return (
          <div 
            className={baseClass}
            style={{ backgroundColor: config.primaryColor }}
          >
            <span className="text-white text-[10px] font-bold">
              {(config.appName || "A")[0].toUpperCase()}
            </span>
          </div>
        );
    }
  };

  const renderMiniScreen = () => {
    const splashStyle = config.splashScreenStyle;

    // Fullscreen / Gradient - full bleed with gradient
    if (splashStyle === "fullscreen" || splashStyle === "gradient") {
      return (
        <div 
          className="w-full h-full flex flex-col items-center justify-center"
          style={{ background: `linear-gradient(135deg, ${config.primaryColor}, ${config.accentColor})` }}
        >
          <div className={cn(
            "rounded-lg overflow-hidden shadow-sm",
            isExpanded ? "w-8 h-8" : "w-5 h-5"
          )}>
            {renderAppIcon()}
          </div>
          {isExpanded && (
            <p className="mt-1 text-white text-[8px] font-medium drop-shadow truncate max-w-full px-1">
              {config.appName || "My App"}
            </p>
          )}
        </div>
      );
    }

    // Minimal - logo at bottom
    if (splashStyle === "minimal") {
      return (
        <div className="w-full h-full flex flex-col items-center justify-end pb-4 bg-white">
          <div className={cn(
            "rounded-lg overflow-hidden shadow-sm",
            isExpanded ? "w-6 h-6" : "w-4 h-4"
          )}>
            {renderAppIcon()}
          </div>
        </div>
      );
    }

    // Animated - with dots
    if (splashStyle === "animated") {
      return (
        <div className="w-full h-full flex flex-col items-center justify-center bg-white">
          <div className={cn(
            "rounded-lg overflow-hidden shadow-sm",
            isExpanded ? "w-8 h-8" : "w-5 h-5"
          )}>
            {renderAppIcon()}
          </div>
          {isExpanded && (
            <>
              <p className="mt-1 text-[8px] font-medium truncate max-w-full px-1" style={{ color: config.primaryColor }}>
                {config.appName || "My App"}
              </p>
              <div className="flex gap-0.5 mt-1">
                <div className="w-1 h-1 rounded-full animate-pulse" style={{ backgroundColor: config.primaryColor }} />
                <div className="w-1 h-1 rounded-full animate-pulse opacity-60" style={{ backgroundColor: config.primaryColor }} />
                <div className="w-1 h-1 rounded-full animate-pulse opacity-30" style={{ backgroundColor: config.primaryColor }} />
              </div>
            </>
          )}
        </div>
      );
    }

    // Default: centered-logo
    return (
      <div className="w-full h-full flex flex-col items-center justify-center bg-white">
        <div className={cn(
          "rounded-lg overflow-hidden shadow-sm",
          isExpanded ? "w-8 h-8" : "w-5 h-5"
        )}>
          {renderAppIcon()}
        </div>
        {isExpanded && (
          <p className="mt-1 text-[8px] font-medium truncate max-w-full px-1" style={{ color: config.primaryColor }}>
            {config.appName || "My App"}
          </p>
        )}
      </div>
    );
  };

  if (!isVisible) {
    return (
      <motion.button
        initial={{ scale: 0, opacity: 0 }}
        animate={{ scale: 1, opacity: 1 }}
        className="fixed bottom-36 left-1/2 -translate-x-1/2 sm:left-auto sm:translate-x-0 sm:right-4 sm:bottom-20 z-40 w-12 h-12 xs:w-14 xs:h-14 rounded-full bg-card border border-border shadow-lg flex items-center justify-center"
        onClick={() => setIsVisible(true)}
      >
        <div className="w-6 h-6 xs:w-7 xs:h-7 rounded-lg overflow-hidden">
          {renderAppIcon()}
        </div>
      </motion.button>
    );
  }

  return (
    <AnimatePresence>
      <motion.div
        initial={{ scale: 0.8, opacity: 0, y: 20 }}
        animate={{ scale: 1, opacity: 1, y: 0 }}
        exit={{ scale: 0.8, opacity: 0, y: 20 }}
        transition={{ type: "spring", damping: 20, stiffness: 300 }}
        className={cn(
          "fixed z-40 bg-zinc-900 rounded-2xl shadow-2xl overflow-hidden transition-all duration-300",
          isExpanded 
            ? "bottom-28 left-1/2 -translate-x-1/2 sm:left-auto sm:translate-x-0 sm:right-4 sm:bottom-20 w-32 xs:w-36 sm:w-28 h-56 xs:h-60 sm:h-48" 
            : "bottom-36 left-1/2 -translate-x-1/2 sm:left-auto sm:translate-x-0 sm:right-4 sm:bottom-20 w-16 xs:w-18 sm:w-16 h-28 xs:h-32 sm:h-28"
        )}
      >
        {/* Mini device frame */}
        <div className="relative w-full h-full p-1">
          {/* Notch */}
          <div className={cn(
            "absolute top-1 left-1/2 -translate-x-1/2 bg-black rounded-full z-20",
            isExpanded ? "w-8 h-2" : "w-5 h-1"
          )} />
          
          {/* Screen */}
          <div className="w-full h-full bg-white rounded-xl overflow-hidden">
            {/* Mini status bar */}
            <div className={cn(
              "flex items-center justify-between px-1 bg-white/90",
              isExpanded ? "text-[6px] pt-2 pb-0.5" : "text-[4px] pt-1"
            )}>
              <span className="text-zinc-500">9:41</span>
              <div className="flex gap-0.5">
                <div className="w-1 h-1 rounded-full bg-zinc-400" />
                <div className="w-1.5 h-1 rounded-sm bg-zinc-400" />
              </div>
            </div>
            
            {/* Content */}
            <div className="flex-1 h-[calc(100%-12px)]">
              {renderMiniScreen()}
            </div>

            {/* Bottom nav indicator */}
            {config.navigationStyle === "bottom-nav" && (
              <div className={cn(
                "absolute bottom-0 left-0 right-0 bg-white/95 border-t border-gray-200 flex justify-around items-center",
                isExpanded ? "py-1.5 px-1" : "py-1 px-0.5"
              )}>
                {navItems.map((item, i) => {
                  const Icon = getIconComponent(item.icon);
                  return (
                    <Icon 
                      key={`${item.label}-${i}`}
                      className={isExpanded ? "w-2.5 h-2.5" : "w-1.5 h-1.5"}
                      style={{ color: i === 0 ? config.primaryColor : "#9CA3AF" }}
                      strokeWidth={i === 0 ? 2.5 : 2}
                    />
                  );
                })}
              </div>
            )}
          </div>

          {/* Control buttons */}
          <div className="absolute -top-1 -right-1 flex gap-0.5">
            <button
              onClick={() => setIsExpanded(!isExpanded)}
              className="w-5 h-5 rounded-full bg-card border border-border shadow-sm flex items-center justify-center hover:bg-secondary transition-colors"
            >
              {isExpanded ? (
                <Minimize2 className="w-2.5 h-2.5 text-muted-foreground" />
              ) : (
                <Maximize2 className="w-2.5 h-2.5 text-muted-foreground" />
              )}
            </button>
            <button
              onClick={() => setIsVisible(false)}
              className="w-5 h-5 rounded-full bg-card border border-border shadow-sm flex items-center justify-center hover:bg-destructive/10 transition-colors"
            >
              <X className="w-2.5 h-2.5 text-muted-foreground" />
            </button>
          </div>
        </div>

        {/* Tap to expand hint */}
        {!isExpanded && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ delay: 1 }}
            className="absolute bottom-1 left-0 right-0 text-center"
          >
            <span className="text-[6px] text-zinc-400">Tap to expand</span>
          </motion.div>
        )}
      </motion.div>
    </AnimatePresence>
  );
};

export default MiniPhonePreview;
