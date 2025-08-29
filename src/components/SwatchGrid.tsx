/**
 * SwatchGrid Component
 * Virtualized color swatch grid with accessibility and premium features
 */

import React from 'react';
import { HairColor } from '../types/color';

export interface SwatchGridProps {
  colors: HairColor[];
  selectedId?: string;
  onSelect: (id: string) => void;
  suggestedOnly?: boolean;
  lockedIds?: string[];
  className?: string;
}

export const SwatchGrid: React.FC<SwatchGridProps> = ({
  colors,
  selectedId,
  onSelect,
  suggestedOnly = false,
  lockedIds = [],
  className = ''
}) => {
  const [hoveredId, setHoveredId] = React.useState<string | null>(null);

  // Filter colors if suggestedOnly is true
  const displayColors = React.useMemo(() => {
    if (suggestedOnly) {
      // Show only colors that are commonly used as accents
      return colors.filter(color => 
        ['blonde', 'fashion', 'pastel', 'vibrant'].includes(color.family) ||
        color.tags?.includes('accent-friendly')
      );
    }
    return colors;
  }, [colors, suggestedOnly]);

  // Determine if we need virtualization (>24 items)
  const needsVirtualization = displayColors.length > 24;

  const renderSwatch = (color: HairColor, index: number) => {
    const isSelected = selectedId === color.id;
    const isLocked = lockedIds.includes(color.id);
    const isHovered = hoveredId === color.id;

    return (
      <button
        key={color.id}
        onClick={() => !isLocked && onSelect(color.id)}
        onMouseEnter={() => setHoveredId(color.id)}
        onMouseLeave={() => setHoveredId(null)}
        onFocus={() => setHoveredId(color.id)}
        onBlur={() => setHoveredId(null)}
        disabled={isLocked}
        className={`
          relative w-12 h-12 rounded-lg border-2 transition-all duration-200
          focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2
          ${isSelected 
            ? 'border-purple-500 shadow-lg transform scale-110' 
            : 'border-gray-300 hover:border-gray-400'
          }
          ${isLocked ? 'cursor-not-allowed opacity-60' : 'cursor-pointer hover:scale-105'}
          ${isHovered ? 'shadow-md' : ''}
        `}
        aria-label={`${color.name} hair color${isLocked ? ' (premium)' : ''}${isSelected ? ' (selected)' : ''}`}
        title={color.name}
      >
        {/* Color swatch */}
        <div
          className="w-full h-full rounded-md"
          style={{ backgroundColor: color.hex }}
        />
        
        {/* Premium lock overlay */}
        {isLocked && (
          <div className="absolute inset-0 bg-black bg-opacity-40 rounded-md flex items-center justify-center">
            <span className="text-white text-lg">🔒</span>
          </div>
        )}
        
        {/* Selection indicator */}
        {isSelected && (
          <div className="absolute -top-1 -right-1 w-4 h-4 bg-purple-500 rounded-full flex items-center justify-center">
            <svg className="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
              <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
            </svg>
          </div>
        )}
        
        {/* Premium badge */}
        {color.isPremium && !isLocked && (
          <div className="absolute -top-1 -left-1 w-4 h-4 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center">
            <span className="text-white text-xs">💎</span>
          </div>
        )}
      </button>
    );
  };

  // Tooltip for hovered color
  const hoveredColor = hoveredId ? displayColors.find(c => c.id === hoveredId) : null;

  if (needsVirtualization) {
    // Virtualized grid for large color sets
    return (
      <div className={`relative ${className}`}>
        <div className="grid grid-cols-6 md:grid-cols-8 gap-2 max-h-48 overflow-y-auto">
          {displayColors.map((color, index) => renderSwatch(color, index))}
        </div>
        
        {/* Floating tooltip */}
        {hoveredColor && (
          <div className="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-1 bg-black bg-opacity-80 text-white text-sm rounded-lg whitespace-nowrap z-10">
            {hoveredColor.name}
            {hoveredColor.isPremium && <span className="ml-1">💎</span>}
          </div>
        )}
      </div>
    );
  }

  // Standard grid for smaller sets
  return (
    <div className={`relative ${className}`}>
      <div className="grid grid-cols-4 md:grid-cols-6 gap-3">
        {displayColors.map((color, index) => renderSwatch(color, index))}
      </div>
      
      {/* Floating tooltip */}
      {hoveredColor && (
        <div className="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-1 bg-black bg-opacity-80 text-white text-sm rounded-lg whitespace-nowrap z-10">
          {hoveredColor.name}
          {hoveredColor.isPremium && <span className="ml-1">💎</span>}
          {hoveredColor.description && (
            <div className="text-xs text-gray-300 mt-1">
              {hoveredColor.description}
            </div>
          )}
        </div>
      )}
    </div>
  );
};

export default SwatchGrid;
