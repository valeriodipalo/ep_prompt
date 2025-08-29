/**
 * Color Utilities
 * Contrast calculation, validation, and suggestions
 */

import { HairColor, ColorChoice, ValidationResult, ColorSuggestion } from '../types/color';

/**
 * Calculate relative luminance of a color
 */
function getLuminance(hex: string): number {
  const rgb = hexToRgb(hex);
  if (!rgb) return 0;
  
  const [r, g, b] = [rgb.r, rgb.g, rgb.b].map(c => {
    c = c / 255;
    return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
  });
  
  return 0.2126 * r + 0.7152 * g + 0.0722 * b;
}

/**
 * Convert hex to RGB
 */
function hexToRgb(hex: string): { r: number; g: number; b: number } | null {
  const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
  return result ? {
    r: parseInt(result[1], 16),
    g: parseInt(result[2], 16),
    b: parseInt(result[3], 16)
  } : null;
}

/**
 * Calculate contrast ratio between two colors
 * Returns value from 1 (no contrast) to 21 (maximum contrast)
 */
export function getContrast(baseHex: string, accentHex: string): number {
  const lum1 = getLuminance(baseHex);
  const lum2 = getLuminance(accentHex);
  
  const brightest = Math.max(lum1, lum2);
  const darkest = Math.min(lum1, lum2);
  
  return (brightest + 0.05) / (darkest + 0.05);
}

/**
 * Suggest accent colors that work well with a base color
 */
export function suggestAccents(baseId: string, palette: HairColor[]): ColorSuggestion[] {
  const baseColor = palette.find(c => c.id === baseId);
  if (!baseColor) return [];
  
  const suggestions: ColorSuggestion[] = [];
  
  for (const color of palette) {
    if (color.id === baseId) continue;
    
    const contrast = getContrast(baseColor.hex, color.hex);
    let reason = '';
    
    // Determine the reason for suggestion based on color families and contrast
    if (contrast >= 4) {
      if (baseColor.family === 'brunette' && color.family === 'blonde') {
        reason = 'Classic brunette-to-blonde contrast';
      } else if (baseColor.family === 'black' && color.family === 'fashion') {
        reason = 'Dramatic color pop on dark base';
      } else if (baseColor.family === 'blonde' && color.family === 'pastel') {
        reason = 'Perfect light base for pastels';
      } else if (contrast >= 7) {
        reason = 'High contrast for bold looks';
      } else {
        reason = 'Good contrast for dimension';
      }
      
      suggestions.push({
        color,
        contrast,
        reason
      });
    }
  }
  
  // Sort by contrast and return top 6
  return suggestions
    .sort((a, b) => b.contrast - a.contrast)
    .slice(0, 6);
}

/**
 * Validate a color choice
 */
export function validateChoice(choice: ColorChoice, palette: HairColor[]): ValidationResult {
  if (choice.type === 'single-tone') {
    const color = palette.find(c => c.id === choice.singleTone?.colorId);
    if (!color) {
      return { ok: false, reason: 'Selected color not found' };
    }
    return { ok: true };
  }
  
  if (choice.type === 'accented' && choice.accented) {
    const baseColor = palette.find(c => c.id === choice.accented!.baseColorId);
    const accentColor = palette.find(c => c.id === choice.accented!.accentColorId);
    
    if (!baseColor) {
      return { ok: false, reason: 'Base color not found' };
    }
    
    if (!accentColor) {
      return { ok: false, reason: 'Accent color not found' };
    }
    
    const contrast = getContrast(baseColor.hex, accentColor.hex);
    
    if (contrast < 1.5) {
      const suggestions = suggestAccents(baseColor.id, palette);
      const suggestion = suggestions[0]?.color.name || 'a different color';
      
      return {
        ok: false,
        reason: 'Accent too close to base; try ' + suggestion,
        suggestion
      };
    }
    
    // Additional validation for technique-specific requirements
    if (choice.accented.technique === 'ombre' && choice.accented.placement === 'overall') {
      return {
        ok: false,
        reason: 'Ombré technique works best with mid-ends or tips placement'
      };
    }
    
    if (choice.accented.technique === 'lowlights' && contrast > 10) {
      return {
        ok: false,
        reason: 'Lowlights should be subtle; try reducing contrast or use highlights instead'
      };
    }
    
    return { ok: true };
  }
  
  return { ok: false, reason: 'Invalid color choice' };
}

/**
 * Get complementary colors based on color theory
 */
export function getComplementaryColors(baseColor: HairColor, palette: HairColor[]): HairColor[] {
  const familyComplements: Record<string, string[]> = {
    'black': ['blonde', 'fashion', 'vibrant'],
    'brunette': ['blonde', 'red', 'fashion'],
    'blonde': ['brunette', 'red', 'pastel'],
    'red': ['brunette', 'blonde', 'fashion'],
    'gray': ['fashion', 'vibrant', 'pastel'],
    'platinum': ['vibrant', 'pastel', 'fashion'],
    'fashion': ['black', 'brunette', 'platinum'],
    'pastel': ['blonde', 'platinum', 'gray'],
    'vibrant': ['black', 'gray', 'platinum']
  };
  
  const complementFamilies = familyComplements[baseColor.family] || [];
  
  return palette.filter(color => 
    color.id !== baseColor.id && 
    complementFamilies.includes(color.family)
  );
}

/**
 * Calculate color temperature (warm/cool)
 */
export function getColorTemperature(hex: string): 'warm' | 'cool' | 'neutral' {
  const rgb = hexToRgb(hex);
  if (!rgb) return 'neutral';
  
  // Simple warm/cool detection based on RGB values
  const warmth = (rgb.r + rgb.g) - (rgb.b * 2);
  
  if (warmth > 50) return 'warm';
  if (warmth < -50) return 'cool';
  return 'neutral';
}

/**
 * Check if a color is suitable for a specific skin tone
 */
export function isSuitableForSkinTone(color: HairColor, skinTone: 'warm' | 'cool' | 'neutral'): boolean {
  const colorTemp = getColorTemperature(color.hex);
  
  if (skinTone === 'neutral') return true;
  if (skinTone === colorTemp) return true;
  
  // Some colors work across temperatures
  const versatileColors = ['brunette', 'black', 'gray'];
  return versatileColors.includes(color.family);
}

/**
 * Get maintenance level for a color choice
 */
export function getMaintenanceLevel(choice: ColorChoice, palette: HairColor[]): 'low' | 'medium' | 'high' {
  if (choice.type === 'single-tone') {
    const color = palette.find(c => c.id === choice.singleTone?.colorId);
    if (!color) return 'medium';
    
    if (['black', 'brunette', 'gray'].includes(color.family)) return 'low';
    if (['platinum', 'vibrant', 'pastel'].includes(color.family)) return 'high';
    return 'medium';
  }
  
  if (choice.type === 'accented' && choice.accented) {
    const accentColor = palette.find(c => c.id === choice.accented!.accentColorId);
    if (!accentColor) return 'medium';
    
    if (['platinum', 'vibrant', 'pastel'].includes(accentColor.family)) return 'high';
    if (choice.accented.intensity === 'bold') return 'high';
    if (choice.accented.technique === 'highlights') return 'high';
    
    return 'medium';
  }
  
  return 'medium';
}
