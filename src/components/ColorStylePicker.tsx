/**
 * ColorStylePicker Component
 * Professional hair color selection with single-tone and accented options
 */

import React from 'react';
import { ColorChoice, HairColor, AccentTechnique, ColorPreset } from '../types/color';
import SwatchGrid from './SwatchGrid';
import { suggestAccents, validateChoice, getContrast } from '../utils/colorUtils';
import { toPrompt } from '../utils/promptMapper';
import colorPresets from '../data/colorPresets';

export interface ColorStylePickerProps {
  value: ColorChoice;
  onChange: (choice: ColorChoice) => void;
  palette: HairColor[];
  className?: string;
  lockedIds?: string[];
}

export const ColorStylePicker: React.FC<ColorStylePickerProps> = ({
  value,
  onChange,
  palette,
  className = '',
  lockedIds = []
}) => {
  const [activeTab, setActiveTab] = React.useState<'single-tone' | 'accented'>(value.type);
  const [showAdvanced, setShowAdvanced] = React.useState(false);

  // Sync tab with value type
  React.useEffect(() => {
    setActiveTab(value.type);
  }, [value.type]);

  // Get suggested accents for current base color
  const suggestedAccents = React.useMemo(() => {
    if (value.type === 'accented' && value.accented?.baseColorId) {
      return suggestAccents(value.accented.baseColorId, palette).map(s => s.color);
    }
    return [];
  }, [value.type, value.accented?.baseColorId, palette]);

  // Validate current choice
  const validation = React.useMemo(() => {
    return validateChoice(value, palette);
  }, [value, palette]);

  const handleTabChange = (tab: 'single-tone' | 'accented') => {
    setActiveTab(tab);
    
    if (tab === 'single-tone') {
      onChange({
        type: 'single-tone',
        singleTone: { colorId: palette.find(c => !c.isPremium)?.id || 'medium-brown' }
      });
    } else {
      onChange({
        type: 'accented',
        accented: {
          baseColorId: palette.find(c => !c.isPremium)?.id || 'medium-brown',
          accentColorId: palette.find(c => c.family === 'blonde')?.id || 'golden-blonde',
          technique: 'highlights',
          intensity: 'standard',
          placement: 'face-framing',
          blend: 'soft'
        }
      });
    }
  };

  const handleSingleToneSelect = (colorId: string) => {
    onChange({
      type: 'single-tone',
      singleTone: { colorId }
    });
  };

  const handleAccentedUpdate = (updates: Partial<NonNullable<ColorChoice['accented']>>) => {
    if (value.type === 'accented' && value.accented) {
      onChange({
        type: 'accented',
        accented: { ...value.accented, ...updates }
      });
    }
  };

  const handlePresetSelect = (preset: ColorPreset) => {
    onChange(preset.choice);
  };

  const currentBaseColor = value.type === 'accented' && value.accented 
    ? palette.find(c => c.id === value.accented!.baseColorId)
    : null;

  const currentAccentColor = value.type === 'accented' && value.accented 
    ? palette.find(c => c.id === value.accented!.accentColorId)
    : null;

  const contrastRatio = currentBaseColor && currentAccentColor 
    ? getContrast(currentBaseColor.hex, currentAccentColor.hex)
    : 0;

  return (
    <div className={`color-style-picker ${className}`}>
      {/* Tab Navigation */}
      <div className="flex bg-gray-100 rounded-lg p-1 mb-6">
        <button
          onClick={() => handleTabChange('single-tone')}
          className={`flex-1 py-2 px-4 text-sm font-medium rounded-md transition-colors ${
            activeTab === 'single-tone'
              ? 'bg-white text-purple-600 shadow-sm'
              : 'text-gray-600 hover:text-gray-800'
          }`}
          aria-pressed={activeTab === 'single-tone'}
        >
          Single-Tone
        </button>
        <button
          onClick={() => handleTabChange('accented')}
          className={`flex-1 py-2 px-4 text-sm font-medium rounded-md transition-colors ${
            activeTab === 'accented'
              ? 'bg-white text-purple-600 shadow-sm'
              : 'text-gray-600 hover:text-gray-800'
          }`}
          aria-pressed={activeTab === 'accented'}
        >
          Accented
        </button>
      </div>

      {/* Single-Tone Tab */}
      {activeTab === 'single-tone' && (
        <div className="space-y-4">
          <div>
            <h3 className="text-lg font-semibold mb-3">Choose Your Color</h3>
            <SwatchGrid
              colors={palette}
              selectedId={value.singleTone?.colorId}
              onSelect={handleSingleToneSelect}
              lockedIds={lockedIds}
            />
          </div>
          
          {/* Selected color info */}
          {value.singleTone?.colorId && (
            <div className="bg-gray-50 rounded-lg p-4">
              <div className="flex items-center space-x-3">
                <div
                  className="w-8 h-8 rounded-full border-2 border-gray-300"
                  style={{ backgroundColor: palette.find(c => c.id === value.singleTone!.colorId)?.hex }}
                />
                <div>
                  <p className="font-medium">
                    {palette.find(c => c.id === value.singleTone!.colorId)?.name}
                  </p>
                  <p className="text-sm text-gray-600">
                    {palette.find(c => c.id === value.singleTone!.colorId)?.description}
                  </p>
                </div>
              </div>
            </div>
          )}
        </div>
      )}

      {/* Accented Tab */}
      {activeTab === 'accented' && (
        <div className="space-y-6">
          {/* Base Color Selection */}
          <div>
            <h3 className="text-lg font-semibold mb-3">Base Color</h3>
            <SwatchGrid
              colors={palette}
              selectedId={value.accented?.baseColorId}
              onSelect={(colorId) => handleAccentedUpdate({ baseColorId: colorId })}
              lockedIds={lockedIds}
            />
          </div>

          {/* Accent Color Selection */}
          <div>
            <div className="flex items-center justify-between mb-3">
              <h3 className="text-lg font-semibold">Accent Color</h3>
              <button
                onClick={() => setShowAdvanced(!showAdvanced)}
                className="text-sm text-purple-600 hover:text-purple-700 md:hidden"
              >
                {showAdvanced ? 'Simple' : 'Advanced'}
              </button>
            </div>
            <SwatchGrid
              colors={suggestedAccents.length > 0 ? suggestedAccents : palette}
              selectedId={value.accented?.accentColorId}
              onSelect={(colorId) => handleAccentedUpdate({ accentColorId: colorId })}
              suggestedOnly={true}
              lockedIds={lockedIds}
            />
            
            {/* Contrast warning */}
            {!validation.ok && (
              <div className="mt-2 p-2 bg-yellow-50 border border-yellow-200 rounded text-sm text-yellow-800">
                ⚠️ {validation.reason}
              </div>
            )}
            
            {/* Contrast indicator */}
            {contrastRatio > 0 && (
              <div className="mt-2 text-xs text-gray-600">
                Contrast ratio: {contrastRatio.toFixed(1)} 
                <span className={`ml-2 ${contrastRatio >= 3 ? 'text-green-600' : 'text-yellow-600'}`}>
                  {contrastRatio >= 5 ? '✅ Excellent' : contrastRatio >= 3 ? '✅ Good' : '⚠️ Low'}
                </span>
              </div>
            )}
          </div>

          {/* Advanced Controls */}
          <div className={`space-y-4 ${showAdvanced ? 'block' : 'hidden md:block'}`}>
            {/* Technique Selection */}
            <div>
              <h4 className="text-sm font-medium mb-2">Technique</h4>
              <div className="grid grid-cols-2 gap-2">
                {(['highlights', 'lowlights', 'balayage', 'ombre'] as AccentTechnique[]).map((technique) => (
                  <button
                    key={technique}
                    onClick={() => handleAccentedUpdate({ technique })}
                    className={`py-2 px-3 text-sm rounded-lg border transition-colors ${
                      value.accented?.technique === technique
                        ? 'border-purple-500 bg-purple-50 text-purple-700'
                        : 'border-gray-300 hover:border-gray-400'
                    }`}
                    aria-label={`Technique: ${technique}`}
                  >
                    {technique.charAt(0).toUpperCase() + technique.slice(1)}
                  </button>
                ))}
              </div>
            </div>

            {/* Intensity Control */}
            <div>
              <h4 className="text-sm font-medium mb-2">Intensity</h4>
              <div className="flex bg-gray-100 rounded-lg p-1">
                {(['subtle', 'standard', 'bold'] as const).map((intensity) => (
                  <button
                    key={intensity}
                    onClick={() => handleAccentedUpdate({ intensity })}
                    className={`flex-1 py-1 px-2 text-sm rounded-md transition-colors ${
                      value.accented?.intensity === intensity
                        ? 'bg-white text-purple-600 shadow-sm'
                        : 'text-gray-600 hover:text-gray-800'
                    }`}
                    aria-label={`Intensity: ${intensity}`}
                  >
                    {intensity.charAt(0).toUpperCase() + intensity.slice(1)}
                  </button>
                ))}
              </div>
            </div>

            {/* Placement Control */}
            <div>
              <h4 className="text-sm font-medium mb-2">Placement</h4>
              <div className="grid grid-cols-2 gap-2">
                {(['overall', 'face-framing', 'mid-ends', 'tips'] as const).map((placement) => (
                  <button
                    key={placement}
                    onClick={() => handleAccentedUpdate({ placement })}
                    className={`py-2 px-3 text-sm rounded-lg border transition-colors ${
                      value.accented?.placement === placement
                        ? 'border-purple-500 bg-purple-50 text-purple-700'
                        : 'border-gray-300 hover:border-gray-400'
                    }`}
                    aria-label={`Placement: ${placement}`}
                  >
                    {placement.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase())}
                  </button>
                ))}
              </div>
            </div>

            {/* Blend Control */}
            <div>
              <h4 className="text-sm font-medium mb-2">Blend</h4>
              <div className="flex bg-gray-100 rounded-lg p-1">
                {(['soft', 'defined'] as const).map((blend) => (
                  <button
                    key={blend}
                    onClick={() => handleAccentedUpdate({ blend })}
                    className={`flex-1 py-1 px-2 text-sm rounded-md transition-colors ${
                      value.accented?.blend === blend
                        ? 'bg-white text-purple-600 shadow-sm'
                        : 'text-gray-600 hover:text-gray-800'
                    }`}
                    aria-label={`Blend: ${blend}`}
                  >
                    {blend.charAt(0).toUpperCase() + blend.slice(1)}
                  </button>
                ))}
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Presets Carousel */}
      <div className="mt-8">
        <h3 className="text-lg font-semibold mb-4">Popular Presets</h3>
        <div className="flex space-x-3 overflow-x-auto pb-2">
          {colorPresets.map((preset) => {
            const isSelected = JSON.stringify(value) === JSON.stringify(preset.choice);
            
            return (
              <button
                key={preset.id}
                onClick={() => handlePresetSelect(preset)}
                className={`flex-shrink-0 p-3 rounded-lg border text-left min-w-[120px] transition-colors ${
                  isSelected
                    ? 'border-purple-500 bg-purple-50'
                    : 'border-gray-300 hover:border-gray-400'
                }`}
              >
                <div className="text-sm font-medium mb-1">{preset.label}</div>
                <div className="text-xs text-gray-600">{preset.description}</div>
                
                {/* Preview colors */}
                <div className="flex space-x-1 mt-2">
                  {preset.choice.type === 'single-tone' && preset.choice.singleTone && (
                    <div
                      className="w-4 h-4 rounded-full border"
                      style={{ 
                        backgroundColor: palette.find(c => c.id === preset.choice.singleTone!.colorId)?.hex 
                      }}
                    />
                  )}
                  {preset.choice.type === 'accented' && preset.choice.accented && (
                    <>
                      <div
                        className="w-4 h-4 rounded-full border"
                        style={{ 
                          backgroundColor: palette.find(c => c.id === preset.choice.accented!.baseColorId)?.hex 
                        }}
                      />
                      <div
                        className="w-4 h-4 rounded-full border"
                        style={{ 
                          backgroundColor: palette.find(c => c.id === preset.choice.accented!.accentColorId)?.hex 
                        }}
                      />
                    </>
                  )}
                </div>
              </button>
            );
          })}
        </div>
      </div>

      {/* Live Preview */}
      <div className="mt-6 p-4 bg-gray-50 rounded-lg">
        <h4 className="text-sm font-medium mb-2">AI Prompt Preview</h4>
        <p className="text-sm text-gray-700 italic">
          "{toPrompt(value, palette, { preserveStyle: true, professionalTerms: true })}"
        </p>
      </div>
    </div>
  );
};

export default ColorStylePicker;
