/**
 * Color Style System Types for AI Hair Salon
 * Professional-grade color selection with single-tone and accented options
 */

export interface HairColor {
  id: string;
  name: string;
  hex: string;
  family: ColorFamily;
  isPremium: boolean;
  description?: string;
  tags?: string[];
}

export interface ColorChoice {
  type: 'single-tone' | 'accented';
  singleTone?: SingleToneColor;
  accented?: AccentedColor;
}

export interface SingleToneColor {
  colorId: string;
}

export interface AccentedColor {
  baseColorId: string;
  accentColorId: string;
  technique: AccentTechnique;
  intensity: 'subtle' | 'standard' | 'bold';
  placement: 'overall' | 'face-framing' | 'mid-ends' | 'tips';
  blend: 'soft' | 'defined';
}

export type ColorFamily = 
  | 'black'
  | 'brunette' 
  | 'blonde'
  | 'red'
  | 'auburn'
  | 'gray'
  | 'platinum'
  | 'fashion'
  | 'pastel'
  | 'vibrant';

export type AccentTechnique = 
  | 'highlights'
  | 'lowlights' 
  | 'balayage'
  | 'ombre';

export interface ColorPreset {
  id: string;
  label: string;
  choice: ColorChoice;
  previewImage?: string;
  description?: string;
}

export interface ValidationResult {
  ok: boolean;
  reason?: string;
  suggestion?: string;
}

export interface ColorSuggestion {
  color: HairColor;
  contrast: number;
  reason: string;
}
