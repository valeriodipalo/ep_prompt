/**
 * Integration Example: ColorStylePicker with StyleAI Widget
 * Shows how to integrate the professional color system into the main widget
 */

import React from 'react';
import { ColorChoice } from '../types/color';
import ColorStylePicker from './ColorStylePicker';
import defaultPalette from '../data/colors';
import { toPrompt } from '../utils/promptMapper';

export interface ColorStyleIntegrationProps {
  onColorChoiceChange: (choice: ColorChoice) => void;
  userIsPremium: boolean;
}

export const ColorStyleIntegration: React.FC<ColorStyleIntegrationProps> = ({
  onColorChoiceChange,
  userIsPremium
}) => {
  const [colorChoice, setColorChoice] = React.useState<ColorChoice>({
    type: 'single-tone',
    singleTone: { colorId: 'medium-brown' }
  });

  const handleColorChange = (choice: ColorChoice) => {
    setColorChoice(choice);
    onColorChoiceChange(choice);
  };

  // Filter palette based on premium status
  const availablePalette = React.useMemo(() => {
    return defaultPalette.filter(color => !color.isPremium || userIsPremium);
  }, [userIsPremium]);

  // Get locked color IDs for non-premium users
  const lockedIds = React.useMemo(() => {
    if (userIsPremium) return [];
    return defaultPalette.filter(c => c.isPremium).map(c => c.id);
  }, [userIsPremium]);

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="text-center">
        <h2 className="text-2xl font-bold text-gray-800 mb-2">
          Professional Color Selection
        </h2>
        <p className="text-gray-600">
          Choose from salon-quality single-tone colors or advanced accented techniques
        </p>
      </div>

      {/* Color Style Picker */}
      <ColorStylePicker
        value={colorChoice}
        onChange={handleColorChange}
        palette={availablePalette}
        lockedIds={lockedIds}
        className="max-w-2xl mx-auto"
      />

      {/* Generated Prompt Preview */}
      <div className="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-4 border border-purple-200">
        <h3 className="font-semibold text-purple-800 mb-2">AI Generation Prompt</h3>
        <p className="text-sm text-purple-700 italic">
          "{toPrompt(colorChoice, defaultPalette, { preserveStyle: true, professionalTerms: true })}"
        </p>
      </div>

      {/* Usage Statistics */}
      <div className="grid grid-cols-2 gap-4 text-center">
        <div className="bg-white rounded-lg p-4 shadow-sm border">
          <div className="text-2xl font-bold text-green-600">
            {availablePalette.filter(c => !c.isPremium).length}
          </div>
          <div className="text-sm text-gray-600">Free Colors</div>
        </div>
        <div className="bg-white rounded-lg p-4 shadow-sm border">
          <div className="text-2xl font-bold text-purple-600">
            {defaultPalette.filter(c => c.isPremium).length}
          </div>
          <div className="text-sm text-gray-600">Premium Colors</div>
        </div>
      </div>
    </div>
  );
};

export default ColorStyleIntegration;
