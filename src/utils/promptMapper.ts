/**
 * Color Choice to AI Prompt Mapper
 * Converts ColorChoice objects to professional salon prompts for AI generation
 */

import { ColorChoice, HairColor } from '../types/color';

export interface PromptOptions {
  preserveStyle?: boolean;
  includeMaintenanceInfo?: boolean;
  professionalTerms?: boolean;
}

/**
 * Convert ColorChoice to AI generation prompt
 */
export function toPrompt(
  choice: ColorChoice, 
  palette: HairColor[], 
  opts: PromptOptions = {}
): string {
  const { preserveStyle = true, includeMaintenanceInfo = false, professionalTerms = true } = opts;
  
  if (choice.type === 'single-tone' && choice.singleTone) {
    return buildSingleTonePrompt(choice.singleTone.colorId, palette, opts);
  }
  
  if (choice.type === 'accented' && choice.accented) {
    return buildAccentedPrompt(choice.accented, palette, opts);
  }
  
  return 'Natural hair color';
}

/**
 * Build prompt for single-tone color
 */
function buildSingleTonePrompt(
  colorId: string, 
  palette: HairColor[], 
  opts: PromptOptions
): string {
  const color = palette.find(c => c.id === colorId);
  if (!color) return 'Natural hair color';
  
  const { professionalTerms } = opts;
  
  let prompt = '';
  
  if (professionalTerms) {
    prompt = `Professional salon single-process hair color: ${color.name.toLowerCase()}`;
  } else {
    prompt = `Hair colored ${color.name.toLowerCase()}`;
  }
  
  // Add color family specific details
  switch (color.family) {
    case 'black':
      prompt += ', deep and rich with natural shine';
      break;
    case 'brunette':
      prompt += ', warm and dimensional with natural depth';
      break;
    case 'blonde':
      prompt += ', luminous and bright with healthy shine';
      break;
    case 'red':
      prompt += ', vibrant and warm with rich undertones';
      break;
    case 'gray':
      prompt += ', sophisticated and elegant with natural silver tones';
      break;
    case 'platinum':
      prompt += ', ultra-light and cool-toned with metallic finish';
      break;
    case 'fashion':
      prompt += ', trendy and modern with artistic flair';
      break;
    case 'pastel':
      prompt += ', soft and dreamy with ethereal quality';
      break;
    case 'vibrant':
      prompt += ', bold and electric with maximum saturation';
      break;
  }
  
  if (opts.preserveStyle) {
    prompt += '. Maintain the existing hairstyle and cut shape';
  }
  
  return prompt + '.';
}

/**
 * Build prompt for accented color technique
 */
function buildAccentedPrompt(
  accented: NonNullable<ColorChoice['accented']>, 
  palette: HairColor[], 
  opts: PromptOptions
): string {
  const baseColor = palette.find(c => c.id === accented.baseColorId);
  const accentColor = palette.find(c => c.id === accented.accentColorId);
  
  if (!baseColor || !accentColor) return 'Natural hair color';
  
  const { professionalTerms } = opts;
  
  let prompt = '';
  
  // Base color foundation
  if (professionalTerms) {
    prompt = `Professional salon hair coloring: ${baseColor.name.toLowerCase()} base color`;
  } else {
    prompt = `Hair with ${baseColor.name.toLowerCase()} base`;
  }
  
  // Technique application
  switch (accented.technique) {
    case 'highlights':
      prompt += ` with ${accentColor.name.toLowerCase()} highlights`;
      break;
    case 'lowlights':
      prompt += ` with ${accentColor.name.toLowerCase()} lowlights`;
      break;
    case 'balayage':
      prompt += ` with ${accentColor.name.toLowerCase()} balayage technique`;
      break;
    case 'ombre':
      prompt += ` with ${accentColor.name.toLowerCase()} ombré gradient`;
      break;
  }
  
  // Intensity details
  switch (accented.intensity) {
    case 'subtle':
      prompt += ', applied subtly for natural dimension';
      break;
    case 'standard':
      prompt += ', applied with standard salon intensity';
      break;
    case 'bold':
      prompt += ', applied boldly for dramatic contrast';
      break;
  }
  
  // Placement specifics
  switch (accented.placement) {
    case 'overall':
      prompt += ' throughout the entire hair';
      break;
    case 'face-framing':
      prompt += ' focused around the face for brightening effect';
      break;
    case 'mid-ends':
      prompt += ' concentrated on the mid-lengths to ends';
      break;
    case 'tips':
      prompt += ' applied only to the hair tips';
      break;
  }
  
  // Blend characteristics
  switch (accented.blend) {
    case 'soft':
      prompt += ' with soft, seamless blending';
      break;
    case 'defined':
      prompt += ' with defined, intentional contrast';
      break;
  }
  
  // Technique-specific enhancements
  if (accented.technique === 'balayage') {
    prompt += '. Hand-painted balayage technique with natural-looking gradients';
  } else if (accented.technique === 'ombre') {
    prompt += '. Smooth ombré transition from dark to light';
  } else if (accented.technique === 'highlights') {
    prompt += '. Professional foil highlights with precise placement';
  } else if (accented.technique === 'lowlights') {
    prompt += '. Strategic lowlights for added depth and dimension';
  }
  
  if (opts.preserveStyle) {
    prompt += '. Maintain the existing hairstyle and cut shape';
  }
  
  return prompt + '.';
}

/**
 * Generate technique-specific prompts
 */
export function getTechniquePrompt(technique: string): string {
  const prompts: Record<string, string> = {
    'highlights': 'Professional foil highlights with precise sectioning',
    'lowlights': 'Strategic lowlights for natural depth and dimension',
    'balayage': 'Hand-painted balayage with natural sun-kissed gradients',
    'ombre': 'Smooth ombré gradient transition with seamless blending'
  };
  
  return prompts[technique] || 'Professional color application';
}

/**
 * Generate intensity-specific modifiers
 */
export function getIntensityModifier(intensity: string): string {
  const modifiers: Record<string, string> = {
    'subtle': 'delicate and natural-looking',
    'standard': 'professionally applied with salon quality',
    'bold': 'dramatic and high-contrast'
  };
  
  return modifiers[intensity] || 'professionally applied';
}

/**
 * Generate placement-specific descriptions
 */
export function getPlacementDescription(placement: string): string {
  const descriptions: Record<string, string> = {
    'overall': 'evenly distributed throughout all hair sections',
    'face-framing': 'strategically placed around the face for brightening',
    'mid-ends': 'concentrated from mid-lengths to ends for modern appeal',
    'tips': 'applied to hair ends for subtle color interest'
  };
  
  return descriptions[placement] || 'professionally placed';
}

/**
 * Create a simplified prompt for quick generation
 */
export function toSimplePrompt(choice: ColorChoice, palette: HairColor[]): string {
  if (choice.type === 'single-tone' && choice.singleTone) {
    const color = palette.find(c => c.id === choice.singleTone!.colorId);
    return color ? `${color.name.toLowerCase()} hair color` : 'natural hair';
  }
  
  if (choice.type === 'accented' && choice.accented) {
    const baseColor = palette.find(c => c.id === choice.accented!.baseColorId);
    const accentColor = palette.find(c => c.id === choice.accented!.accentColorId);
    
    if (baseColor && accentColor) {
      return `${baseColor.name.toLowerCase()} hair with ${accentColor.name.toLowerCase()} ${choice.accented.technique}`;
    }
  }
  
  return 'natural hair color';
}

/**
 * Export ready for Fal/Flux call site
 */
export default {
  toPrompt,
  toSimplePrompt,
  getTechniquePrompt,
  getIntensityModifier,
  getPlacementDescription
};
